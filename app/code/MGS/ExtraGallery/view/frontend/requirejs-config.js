var config = {
	"map": {
		"*": {
			"zoom-images": "MGS_ExtraGallery/js/jquery.zoom.min"
		}
	},
	"paths": {
		"zoom-images": "MGS_ExtraGallery/js/jquery.zoom.min"
	},
    "shim": {
		"MGS_ExtraGallery/js/jquery.zoom.min": ["jquery"]
	},
	config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'MGS_ExtraGallery/js/configurable': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'MGS_ExtraGallery/js/swatch-renderer': true
            }
        }
    }
};
