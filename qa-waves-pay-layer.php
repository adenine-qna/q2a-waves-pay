<?php

class qa_html_theme_layer extends qa_html_theme_base
{
	public function header()
	{
		if (isset($this->content['navigation']) && isset($this->content['navigation']['sub']) &&
			isset($this->content['navigation']['sub']['admin/general'])) {
			
			// Adding the Waves Assets page to the admin sub menu
			$path = 'admin/waves-assets';
			$this->content['navigation']['sub'][$path] = array (
				"label" => qa_lang_html('plugin_waves_pay_desc/waves_assets'),
				"url" => qa_path($path),
				"selected" => substr(qa_request(), 0, strlen($path)) === $path ? 1 : 0,
			);
			
			// Adding the Waves Account page to the admin sub menu
			$path = 'admin/waves-acct';
			$this->content['navigation']['sub'][$path] = array (
				"label" => qa_lang_html('plugin_waves_pay_desc/waves_acct'),
				"url" => qa_path($path),
				"selected" => substr(qa_request(), 0, strlen($path)) === $path ? 1 : 0,
			);
			
			// Adding the Waves Payments page to the admin sub menu
			$path = 'admin/waves-payments';
			$this->content['navigation']['sub'][$path] = array (
				"label" => qa_lang_html('plugin_waves_pay_desc/waves_payments'),
				"url" => qa_path($path),
				"selected" => substr(qa_request(), 0, strlen($path)) === $path ? 1 : 0,
			);
		}
		
		parent::header(); // call back through to the default function
	}
}
