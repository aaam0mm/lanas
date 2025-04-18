<?php

/**
 * functions.php
 * This file contain all most used and neccesary function for admin
 */
if (!function_exists("admin_dash_menu")) {
	/**
	 * admin_dash_menu()
	 */
	function admin_dash_menu()
	{
		global $arr, $u;

		if (is_array($arr)) {
			$html = "";
			$class_attr = "";
			$page = isset($_GET["page"]) ? $_GET["page"] : '';
			foreach ($arr as $k => $v) {
				$dash_m_title = $v["title"];
				$dash_m_icon = $v["icon"];
				$dash_m_link = siteurl() . '/admin/dashboard/' . $v["link"];
				$dash_m_childs = $v["childs"];
				if (is_bool($dash_m_childs)) {
					$dash_child_titles = @$v["childs"]["title"];
					$dash_child_icons = @$v["childs"]["icon"];
					$dash_child_links = @$v["childs"]["link"];
					if (admin_authority()->$k == "on") {
						$html .= '<a href="' . $dash_m_link . '">';
						$html .= '<li class="no_child ' . $class_attr . '">';
						$html .= '<span><i class="fa fa-' . $dash_m_icon . '"></i>&nbsp;' . $dash_m_title . '</span>';
						$html .= '</li>';
						$html .= '</a>';
					}
				} else {
					if (admin_authority()->$k == "on") {
						$html .= '<li class="show-child ' . $class_attr . '">';
						$html .= '<span><i class="fa fa-' . $dash_m_icon . '"></i>&nbsp;' . $dash_m_title . '</span>';
						$html .= '<ul class="had_child">';
						foreach ($dash_m_childs as $child_k => $child_v) {
							$html .= '<a href="' . siteurl() . '/admin/dashboard/' . $child_v["link"] . '"><dl>' . $child_v["title"] . '</dl></a>';
						}
						$html .= '</ul>';
						$html .= '</li>';
					}
				}
			}
			return $html;
		}
	}
}

if (!function_exists("multi_input_languages")) {
	/**
	 * multi_input_languages()
	 *
	 * @param string $input_name
	 * @param string $input_type
	 * @param mixed $data (boolean|object)
	 * @return string HTML Markup
	 */
	function multi_input_languages($input_name, $input_type, $data = false)
	{
		$data_output = "";
		// $visibiliy_lang = "on";
		foreach (get_langs(null, null) as $lang_k => $lang_v) {
			$lang_code = $lang_v["lang_code"];
			$lang_icon = $lang_v["lang_icon"];
			$lang_name = $lang_v["lang_name"];
			$lang_visibility = $lang_v["lang_visibility"];
			if ($data) {
				if (is_object($data) === false) {
					$data = json_decode($data);
				}
				$data_output = $data->$lang_code ?? "";
			}
			$html = '';
			if ($lang_visibility == "on") {
				$html .= "<div class='multi_lang_input line-elm-flex'>";
				$html .= "<div class='multi_lang_i_n r3-width'><span>" . $lang_name . "</span></div>";
				$html .= "<div class='multi_lang_inp r7-width'>";
				if ($input_type == "text") {
					$html .= "<input type='text' name='$input_name" . "[$lang_code]' value='$data_output'/>";
				}
				if ($input_type == "textarea") {
					$html .= "<textarea name='$input_name" . "[$lang_code]' class='tinymce-area'>$data_output</textarea>";
				}
				$html .= "</div>";
				$html .= "</div>";
			} else {
				$html .= "<input type='hidden' name='$input_name" . "[$lang_code]' value='$data_output'/>";
			}
			echo $html;
		}
	}
}
