<?php if (!defined('PHRACTAL')) { exit('no access'); }
/**
 * phractal
 *
 * A framework for PHP 5 dedicated to high availability and scaling.
 *
 * @author		Matthew Barlocker
 * @copyright	Copyright (c) 2011, Matthew Barlocker
 * @license		Proprietary, All Rights Reserved
 * @link		https://github.com/mbarlocker/phractal
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Thrown when a template cannot be used because it doesn't exist or isn't readable.
 */
class PhractalBaseViewInvalidTemplatePathException extends PhractalNameException {}

// ------------------------------------------------------------------------

/**
 * View Base Class
 *
 * Takes inputs from the controllers and outputs the data
 * in the requested format.
 */
class PhractalBaseView extends PhractalObject
{
	/**
	 * Data variables to use in the view
	 * 
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Array of templates paths to use when rendering. Each path
	 * is relative to the APP/view/templates directory.
	 * 
	 * @var array
	 */
	protected $templates = array();
	
	/**
	 * The main, and innermost, template to render. This will correspond
	 * to the most specific data to be rendered. This will never be a
	 * content template.
	 * 
	 * @var string
	 */
	protected $main_template;
	
	/**
	 * Constructor
	 * 
	 * @param string $main_template Main template file to render inside of
	 *                              all layouts and templates.
	 */
	public function __construct($main_template)
	{
		parent::__construct();
		
		$this->main_template = $main_template;
	}
	
	/**
	 * Get the main template to render
	 * 
	 * @return string
	 */
	public function get_main_template()
	{
		return $this->main_template;
	}
	
	/**
	 * Set the main template
	 * 
	 * Path must be relative to APP/view/templates, and must be
	 * extensionless (no .php)
	 * 
	 * @param string $main_template
	 */
	public function set_main_template($main_template)
	{
		$this->main_template = $main_template;
	}
	
	/**
	 * Reset the view.
	 * Delete all data variables.
	 * Delete all scheduled templates
	 */
	public function reset()
	{
		$this->data = array();
		$this->templates = array();
	}
	
	/**
	 * Render the templates, beginning with the first (innermost) template,
	 * and working out to the last (outermost) template.
	 * 
	 * @return string Rendered views
	 * @throws PhractalBaseViewInvalidTemplatePathException
	 */
	public function render()
	{
		$content = '';
		
		foreach (array_merge(array($this->main_template), $this->templates) as $template)
		{
			$absolute = PATH_APP . '/view/templates/' . $template . '.php';
			
			if (!is_file($absolute) || !is_readable($absolute))
			{
				throw new PhractalBaseViewInvalidTemplatePathException($absolute);
			}
			
			ob_start();
			$this->render_no_locals($absolute, $this->data, $content);
			$content = ob_get_clean();
		}
		
		return $content;
	}
	
	/**
	 * Helper function that eliminates all local variables from the scope of the view.
	 * 
	 * @param string $absolute_path
	 * @param array $data
	 * @param string $content Previous content
	 */
	protected function render_no_locals($absolute_path, &$data, $content)
	{
		require($absolute_path);
	}
	
	/**
	 * Add a template file to the front of the process queue.
	 * 
	 * This function should be used for templates specific to page
	 * content, as they will be contained in later views.
	 * 
	 * Path must be relative to APP/view/templates, and must be
	 * extensionless (no .php)
	 * 
	 * @param string $path
	 */
	public function unshift_template($path)
	{
		array_unshift($this->templates, $path);
	}
	
	/**
	 * Remove a template file from the front of the process queue.
	 * 
	 * @return string
	 */
	public function shift_template()
	{
		return array_shift($this->templates);
	}
	
	/**
	 * Add a template file to the end of the process queue.
	 * 
	 * This function should be used for generic templates. The template
	 * files should use the variable '$content' to access previously
	 * generated template content.
	 * 
	 * Path must be relative to APP/view/templates, and must be
	 * extensionless (no .php)
	 * 
	 * @param string $path
	 */
	public function push_template($path)
	{
		array_push($this->templates, $path);
	}
	
	/**
	 * Remove a template file from the end of the process queue.
	 * 
	 * @return string
	 */
	public function pop_template()
	{
		return array_pop($this->templates);
	}
	
	/**
	 * Set a variable to use in the view.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}
	
	/**
	 * Set an array of variables to use in the view
	 * 
	 * @param array $array
	 */
	public function set_array(array $array)
	{
		$this->data = array_merge($this->data, $array);
	}
	
	/**
	 * Delete a variable from the view data
	 * 
	 * @param string $name
	 */
	public function delete($name)
	{
		unset($this->data[$name]);
	}
}
