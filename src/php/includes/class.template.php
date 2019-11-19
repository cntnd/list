<?php
/**
 * class Template
 *
 * Light template mechanism
 *
 * @author Thomas D'Ascoli <Thomas@DAsco.li>
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business <http://www.4fb.de>
 * @author Stefan Jelner (Optimizations)
 * @version 2.0
 */
class Template {
	/**
	 * Needles (static)
	 * @var array
	 */
	var $needles = array ();

	/**
	 * Replacements (static)
	 * @var array
	 */
	var $replacements = array ();

	/**
	 * Dyn_Needles (dynamic)
	 * @var array
	 */
	var $Dyn_needles = array ();

	/**
	 * Dyn_Replacements (dynamic)
	 * @var array
	 */
	var $Dyn_replacements = array ();

	/**
	 * Dynamic counter
	 * @var int
	 */
	var $dyn_cnt = 0;

	/**
	 * Tags array (for dynamic blocks);
	 * @var array
	 */
	var $tags = array ('static' => '{%s}', 'start' => '<!-- BEGIN:BLOCK -->', 'end' => '<!-- END:BLOCK -->');

		function __construct($tags = false){

			if (is_array($tags)) {
				$this->tags = $tags;
			}
		} // end function

	/**
	 * Set Templates placeholders and values
	 *
	 * With this method you can replace the placeholders
	 * in the static templates with dynamic data.
	 *
	 * @param $which String 's' for Static or else dynamic
	 * @param $needle String Placeholder
	 * @param $replacement String Replacement String
	 *
	 * @return void
	 */
	function set($which = 's', $needle, $replacement)	{
		if ($which == 's'){ // static
			$this->needles[] = sprintf($this->tags['static'], $needle);
			$this->replacements[] = $replacement;

		} else { // dynamic
			$this->Dyn_needles[$this->dyn_cnt][] = sprintf($this->tags['static'], $needle);
			$this->Dyn_replacements[$this->dyn_cnt][] = $replacement;
		}
	}

	/**
	 * Iterate internal counter by one
	 *
	 * @return void
	 */
	function next()	{
		$this->dyn_cnt++;
	}

	/**
	 * Reset template data
	 *
	 * @return void
	 */
	function reset()	{
		$this->dyn_cnt = 0;
		$this->needles = array ();
		$this->replacements = array ();
		$this->Dyn_needles = array ();
		$this->Dyn_replacements = array ();
	}

	/**
	 * Generate the template and
	 * print/return it. (do translations sequentially to save memory!!!)
	 *
	 * @param $template string/file Template
	 * @param $return bool Return or print template
	 *
	 * @return string complete Template string
	 */
	function generate($template, $return = 0) {

		//check if the template is a file or a string
		if (!@ is_file($template)){
			$content = & $template; //template is a string (it is a reference to save memory!!!)
		}
		else {
			$content = implode("", file($template)); //template is a file
		}

		$pieces = array();

		//if content has dynamic blocks
		if (preg_match("/^.*".preg_quote($this->tags['start'], "/").".*?".preg_quote($this->tags['end'], "/").".*$/s", $content)) {
			//split everything into an array
			preg_match_all("/^(.*)".preg_quote($this->tags['start'], "/")."(.*?)".preg_quote($this->tags['end'], "/")."(.*)$/s", $content, $pieces);
			//safe memory
			array_shift($pieces);
			$content = "";
			//now combine pieces together

			//start block
			$content .= str_replace($this->needles, $this->replacements, $pieces[0][0]);
			unset ($pieces[0][0]);

			//generate dynamic blocks
			for ($a = 0; $a < $this->dyn_cnt; $a ++) {
				$content .= str_replace($this->Dyn_needles[$a], $this->Dyn_replacements[$a], $pieces[1][0]);
			}
			unset ($pieces[1][0]);

			//end block
			$content .= str_replace($this->needles, $this->replacements, $pieces[2][0]);
			unset ($pieces[2][0]);
		} else {
			$content = str_replace($this->needles, $this->replacements, $content);
		}

		if ($return)
			return $content;
		else
			echo $content;
	} # end function
} # end class
?>
