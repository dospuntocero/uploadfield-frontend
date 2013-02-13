<?php 

class UploadField_FrontEnd extends UploadField {


	protected $templateFileButtons = 'UploadField_FrontEnd_FileButtons';


	public function Field($properties = array()) {
		$record = $this->getRecord();
		$name = $this->getName();

		// if there is a has_one relation with that name on the record and 
		// allowedMaxFileNumber has not been set, it's wanted to be 1
		if(
			$record && $record->exists()
			&& $record->has_one($name) && !$this->getConfig('allowedMaxFileNumber')
		) {
			$this->setConfig('allowedMaxFileNumber', 1);
		}

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/i18n.js');
		Requirements::javascript(FRAMEWORK_ADMIN_DIR . '/javascript/ssui.core.js');
		Requirements::css("UploadFieldFrontEnd/UploadField.css");
		Requirements::combine_files('uploadfield.js', array(
			THIRDPARTY_DIR . '/javascript-templates/tmpl.js',
			THIRDPARTY_DIR . '/javascript-loadimage/load-image.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.iframe-transport.js',
			THIRDPARTY_DIR . '/jquery-fileupload/cors/jquery.xdr-transport.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.fileupload.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.fileupload-ui.js',
			FRAMEWORK_DIR . '/javascript/UploadField_uploadtemplate.js',
			'UploadFieldFrontEnd/js/UploadField_downloadtemplate.js',
			FRAMEWORK_DIR . '/javascript/UploadField.js',
		));
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css'); // TODO hmmm, remove it?
		Requirements::css(FRAMEWORK_DIR . '/css/UploadField.css');

		$config = array(
			'url' => $this->Link('upload'),
			'urlSelectDialog' => $this->Link('select'),
			'urlAttach' => $this->Link('attach'),
			'acceptFileTypes' => '.+$',
			'maxNumberOfFiles' => $this->getConfig('allowedMaxFileNumber')
		);
		if (count($this->getValidator()->getAllowedExtensions())) {
			$allowedExtensions = $this->getValidator()->getAllowedExtensions();
			$config['acceptFileTypes'] = '(\.|\/)(' . implode('|', $allowedExtensions) . ')$';
			$config['errorMessages']['acceptFileTypes'] = _t(
				'File.INVALIDEXTENSIONSHORT', 
				'Extension is not allowed'
			);
		}
		if ($this->getValidator()->getAllowedMaxFileSize()) {
			$config['maxFileSize'] = $this->getValidator()->getAllowedMaxFileSize();
			$config['errorMessages']['maxFileSize'] = _t(
				'File.TOOLARGESHORT', 
				'Filesize exceeds {size}',
				array('size' => File::format_size($config['maxFileSize']))
			);
		}
		if ($config['maxNumberOfFiles'] > 1) {
			$config['errorMessages']['maxNumberOfFiles'] = _t(
				'UploadField.MAXNUMBEROFFILESSHORT', 
				'Can only upload {count} files',
				array('count' => $config['maxNumberOfFiles'])
			);
		}
		$configOverwrite = array();
		if (is_numeric($config['maxNumberOfFiles']) && $this->getItems()->count()) {
			$configOverwrite['maxNumberOfFiles'] = $config['maxNumberOfFiles'] - $this->getItems()->count();
		}
		
		$config = array_merge($config, $this->ufConfig, $configOverwrite);
		
		return $this->customise(array(
			'configString' => str_replace('"', "'", Convert::raw2json($config)),
			'config' => new ArrayData($config),
			'multiple' => $config['maxNumberOfFiles'] !== 1,
			'displayInput' => (!isset($configOverwrite['maxNumberOfFiles']) || $configOverwrite['maxNumberOfFiles'])
		))->renderWith($this->getTemplates());
	}
	

}

