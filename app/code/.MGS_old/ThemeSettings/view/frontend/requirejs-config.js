var config = {
	"map": {
		"*": {
			"mlazyload": "MGS_ThemeSettings/js/jquery.lazyload",
            "mgsvisible": "MGS_ThemeSettings/js/element_visible",
            "mgsbgvideo": "MGS_ThemeSettings/js/jquery.mb.YTPlayer.src",
			"mrotateImage": "MGS_ThemeSettings/js/j360",
            "mgsslick": "MGS_ThemeSettings/js/slick.min",
            "mgsmasonry": "MGS_ThemeSettings/js/masonryChangeRow.pkgd",
            "MgsModelViewLegacy" : "MGS_ThemeSettings/js/model-ar-legacy",
            "MgsModelViewMin" : "MGS_ThemeSettings/js/model-ar.min",
            "stickyContent": "MGS_ThemeSettings/js/jquery.sticky-kit.min"
		}
	},

	"paths": {
		"mlazyload": "MGS_ThemeSettings/js/jquery.lazyload",
        "mgsvisible": "MGS_ThemeSettings/js/element_visible",
        "mgsbgvideo": "MGS_ThemeSettings/js/jquery.mb.YTPlayer.src",
		"mrotateImage": "MGS_ThemeSettings/js/j360",
        "mgsslick": "MGS_ThemeSettings/js/slick.min",
        "mgsmasonry": "MGS_ThemeSettings/js/masonryChangeRow.pkgd",
        "MgsModelViewLegacy" : "MGS_ThemeSettings/js/model-ar-legacy",
        "MgsModelViewMin" : "MGS_ThemeSettings/js/model-ar.min",
        "stickyContent": "MGS_ThemeSettings/js/jquery.sticky-kit.min",
	},
    "shim": {
		"MGS_ThemeSettings/js/jquery.lazyload": ["jquery"],
        "MGS_ThemeSettings/js/element_visible": ["jquery"],
        "MGS_ThemeSettings/js/jquery.mb.YTPlayer.src": ["jquery"],
		"MGS_ThemeSettings/js/j360": ["jquery"],
        "MGS_ThemeSettings/js/masonryChangeRow.pkgd": ["jquery"],
        "MGS_ThemeSettings/js/model-ar-legacy": ["jquery"],
        "MGS_ThemeSettings/js/model-ar.min": ["jquery"],
        "MGS_ThemeSettings/js/jquery.sticky-kit.min": ["jquery"]
	},
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'MGS_ThemeSettings/js/swatch-renderer': true
            }
        }
    }
};
