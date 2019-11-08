var config = {
    map: {
        '*': {
            wesupplyestimations: 'WeSupply_Toolbox/js/wesupplyestimations',
            wesupplyorderview: 'WeSupply_Toolbox/js/wesupplyorderview',
            loadiframe: 'WeSupply_Toolbox/js/loadiframe'
        }
    },
    shim: {
        wesupplyestimations: {
            deps: ['jquery']
        },
        wesupplyorderview: {
            deps: ['jquery']
        },
        loadiframe: {
            deps: ['jquery']
        }
    }
};