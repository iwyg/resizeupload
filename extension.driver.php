<?php

	Class extension_resizeupload extends Extension{
	
		public function about(){
			return array('name' => 'Resize Uploaded Image Files',
						 'version' => '1',
						 'release-date' => '2011-06-18',
						 'author' => array('name' => 'Thomas Appel',
										   'website' => 'http://thomas-appel.com')
				 		);
		}
		
		public function getSubscribedDelegates(){
			return array(	
					array(
						'page' => '/system/preferences/',
						'delegate' => 'AddCustomPreferenceFieldsets',
						'callback' => '__appendPreferences'
					),				
					array(
						'page' => '/publish/edit/',
						'delegate' => 'EntryPreEdit',
						'callback' => 'resizeUpload'
					),
					array(
						'page' => '/publish/new/',
						'delegate' => 'EntryPreCreate',
						'callback' => 'resizeUpload'
					)				
			);
		}
		public function install() {
			// Add defaults to config.php
			if (!Symphony::Configuration()->get('resizeupload')) {
				Symphony::Configuration()->set('im_path', '/usr/local/bin/', 'resizeupload');
				Symphony::Configuration()->set('max_w', '800', 'resizeupload');
				Symphony::Configuration()->set('max_h', '800', 'resizeupload');
			}
			
			return Administration::instance()->saveConfig();
		}
		
		public function uninstall() {
			Symphony::Configuration()->remove('resizeupload');
			return Administration::instance()->saveConfig();
		}		
		
		public function resizeUpload($context) {
			
			$entry = $context['entry'];
			$content = $entry->getData();
			
			$fM = new FieldManager($this->_Parent);
			
			foreach($content as $i => $field) {				
				if ($fM->fetchFieldTypeFromID($i) == 'upload') {
					# prefilter if field is image
					$current_field = $fM->fetch($i);					
					if (preg_match('/^image+\/.*?/',$field['mimetype'])) {
						$this->processImageFile(&$field, $validator);						
					} 					
				}
			}			
		}
		
		public function processImageFile(&$field, $validator) {
			
			if (!$meta = getimagesize($file = WORKSPACE . $field['file'])) {
				return;				
			}
			
			if (file_exists($file) && is_readable($file)) {
				preg_match('/[^\/]+\.([a-z]+)$/i',$field['file'],$filename);				
				$path = WORKSPACE . preg_replace('/'.$filename[0].'/', '', $field['file']);
				$tempfile = $path . 'temp_' . $filename[0];
				
				rename($file, $tempfile);
				#Symphony::Configuration()->get($name = null, $group)
				$conf = Symphony::Configuration()->get('resizeupload');

				if ($meta[0] > $conf['max_w'] || $meta[1] > $conf['max_h']) {
					
					self::convert($tempfile, $file, $conf['max_w'],$conf['max_h'], $conf['im_path']);
					
					if (file_exists($file) && is_readable($file)) {

						require_once (TOOLKIT . '/fields/field.upload.php');
						#$field['size'] = General::formatFilesize(filesize($file));
						$field['size'] = filesize($file);
						$field['meta'] = serialize(fieldUpload::getMetaInfo($file, $field['mimetype']));					

						unlink($tempfile);					
					} else {
						rename($tempfile, $file);		
					}					
				} else {
					return;
				}
			}
		}
		/** 
		 * Resizes an Image to a given maximum width and height
		 * 
		 * @param string $ifile 
		 * input file
		 * @param string $ofile 
		 * output file
		 * @param string $maxw
		 * maximum width
		 * @param string $maxh 
		 * maximum height
		 * @return void
		 * executes convert
		 */
		
		public static function convert($ifile = null, $ofile = null, $maxw = null, $maxh = null, $impath = NULL){			
			return exec("{$impath}convert $ifile -thumbnail {$maxw}x{$maxh} $ofile");
		}
		
		public function __appendPreferences($context) {
			
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Resize Uploaded Images'));
			
			$label = Widget::Label('Path to ImageMagick / GraphicsMagick');
			$label->appendChild(
				Widget::Input(
					'settings[resizeupload][im_path]',
					General::Sanitize(Symphony::Configuration()->get('im_path', 'resizeupload'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Maximum Image Width');
			$label->appendChild(
				Widget::Input(
					'settings[resizeupload][max_w]',
					General::Sanitize(Symphony::Configuration()->get('max_w', 'resizeupload'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Maximum Image Height');
			$label->appendChild(
				Widget::Input(
					'settings[resizeupload][max_h]',
					General::Sanitize(Symphony::Configuration()->get('max_h', 'resizeupload'))
				)
			);
			$group->appendChild($label);

			$context['wrapper']->appendChild($group);
		}		
		
	}

?>