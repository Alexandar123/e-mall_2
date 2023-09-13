var config = {
	"map": {
		"*": {
			"lookbookUploader": "MGS_Lookbook/js/fileuploader",
			"lookbookAnnotate": "MGS_Lookbook/js/jquery.annotate",
		}
	},
	"paths": {            
		"lookbookUploader": "MGS_Lookbook/js/fileuploader",
		"lookbookAnnotate": "MGS_Lookbook/js/jquery.annotate",
	},   
    "shim": {
		"MGS_Lookbook/js/fileuploader": ["jquery"],
		"MGS_Lookbook/js/jquery.annotate": ["jquery"]
	}
};