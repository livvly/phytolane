<?php
namespace WeltPixel\SpeedOptimization\Controller\Adminhtml\Bundling;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \WeltPixel\SpeedOptimization\Controller\Adminhtml\Items\Create
 */
class Create extends Action
{
    const PATH_JAVASCRIPT_MERGE = 'dev/js/merge_files';
    const PATH_JAVASCRIPT_BUNDLE = 'dev/js/enable_js_bundling';
    const PATH_JAVASCRIPT_MINIFY = 'dev/js/minify_files';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var DirReader
     */
    protected $moduleDirReader;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Version constructor.
     *
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param DirReader $moduleDirReader
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonFactory $resultJsonFactory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        DirReader $moduleDirReader,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory,
        ProductMetadataInterface $productMetadata
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryList = $directoryList;
        $this->moduleDirReader = $moduleDirReader;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        $params = $this->getRequest()->getParams();
        $step = $params['step'];
        switch ($step) {
            case '1':
                $this->_parseStep1($params);
                break;
            case '2':
                $result = $this->_parseStep2($params);
                break;
            case '3':
                $result['msg'] = $this->_parseStep3($params);
                break;
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }

    protected function _parseStep1($params)
    {
        $store = $params['store'];
        $website = $params['website'];
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = 0;

        if ($website) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
            $scopeId = $website;
        }
        if ($store) {
            $scope = ScopeInterface::SCOPE_STORES;
            $scopeId = $store;
        }

        $this->configWriter->save(self::PATH_JAVASCRIPT_BUNDLE, 0, $scope, $scopeId);
        $this->configWriter->save(self::PATH_JAVASCRIPT_MERGE, 0, $scope, $scopeId);
        $this->configWriter->save(self::PATH_JAVASCRIPT_MINIFY, 0, $scope, $scopeId);
    }

    /**
     * @param $params
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _parseStep2($params)
    {
        $result = [];
        $error = false;
        $frontendPath = $this->directoryList->getPath(DirectoryList::STATIC_VIEW) . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
        $themeLocales = $params['themelocales'];
        foreach ($themeLocales as $path) {
            $sourceDir  = $frontendPath . $path;
            $destinationDir  = $sourceDir . '_tmp';
            if (!file_exists($sourceDir)) {
                $result[] = __('There was no static content found for: ' . $path);
                $error = true;
                continue;
            }
            if (!file_exists($destinationDir)) {
                mkdir($destinationDir, 0775);
                $this->copyDirectory($sourceDir, $destinationDir);
                $result[] = __('Prepared content for: ' . $path);
            } else {
                $result[] = __('The content was already created for: ' . $path);
                continue;
            }
        }

        if ($error) {
            $result[] = '<br/>' . __('Make sure your site is on production mode and static content is properly deployed.');
        }

        return [
            'msg' => $result,
            'error' => $error
        ];
    }

    /**
     * @param $params
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _parseStep3($params)
    {
        $result = [];
        $buildJsFile = 'advancedbundling_build.js';
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.0', '<')) {
            $buildJsFile = 'advancedbundling_build_2_2_x.js';
        }
        if (version_compare($magentoVersion, '2.2.6', '<=')) {
            $buildJsFile = 'advancedbundling_build_2_2_6.js';
        }
        $themeLocales = $params['themelocales'];
        $destinationPath = $this->directoryList->getPath(DirectoryList::STATIC_VIEW) . DIRECTORY_SEPARATOR . 'advancedbundling_build.js';
        $buildJsPath = $this->moduleDirReader->getModuleDir('', 'WeltPixel_SpeedOptimization')
            . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $buildJsFile;
        if (file_exists($destinationPath)) {
            $result[] = __('The advancedbundling_build.js file was already added.') . '<br/>';
        } else {
            @copy($buildJsPath, $destinationPath);
            $result[] = __('The advancedbundling_build.js file is ready.') . '<br/>';
        }

        $result[] = __('Execute the following CLI SSH commands from your project\'s root path. Note that require.js needs to be installed for the commands to work. More details about the requirements can be found in the Speed Optimization module documentation.') . '<br/>';
        $commandIterator = 1;
        foreach ($themeLocales as $path) {
            $result[] = __('Command') . ' #' . $commandIterator . '<br/>';
            $result[] = '<b>' . 'node_modules/requirejs/bin/r.js -o pub/static/advancedbundling_build.js baseUrl=pub/static/frontend/'
                . $path . '_tmp dir=pub/static/frontend/' . $path . '</b><br/>';
            $commandIterator +=1;
        }

        $result[] = __("After the commands have been executed, the Advanced Bundling proces is complete. Flush all Caches and reload the frontend.");

        return $result;
    }

    /**
     * @param $source
     * @param $dest
     * @return bool
     */
    protected function copyDirectory($source, $dest)
    {
        $sourceHandle = opendir($source);

        if (!$sourceHandle) {
            return false;
        }

        while ($file = readdir($sourceHandle)) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if (is_dir($source . '/' . $file)) {
                if (!file_exists($dest . '/' . $file)) {
                    @mkdir($dest . '/' . $file, 0775);
                }
                $this->copyDirectory($source . '/' . $file, $dest . '/' . $file);
            } else {
                @copy($source . '/' . $file, $dest . '/' . $file);
            }
        }

        return true;
    }
}
