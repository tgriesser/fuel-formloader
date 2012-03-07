<?php

namespace Formloader;

/**
 * Part of the Formloader package for Fuel
 *
 * @package   Formloader
 * @version   1.0
 * @author    Tim Griesser
 * @license   MIT License
 * @copyright 2012 Tim Griesser
 * @link      http://tgriesser.com
 **/
class Formloader_Forms extends Formloader_Bridge
{
	/**
	 * The defaults array set in the _init()
	 * @var array
	 */
	public static $_defaults;

	/**
	 * Called by fuelPHP when the class is first initialized
	 * ...almost like a static constructor
	 */
	public static function _init()
	{
		/**
		 * The Loopforge array for composing a "Form"
		 * @var array
		 */
		static::$_defaults = array(

			/**
			 * Determines the type of Formloader object we're inheriting from
			 * in the Formloader_Bridge class
			 */
			'object_type' => 'forms',
			
			/**
			 * Required name of the group (used to namespace the form items)
			 * @var String
			 * @throws \FormloaderException
			 */			
			'group'  => function()
			{
				throw new \FormloaderException('A group is needed in order to deal with this properly');
			},
			
			/**
			 * Required name of the action
			 * @var String
			 * @throws \FormloaderException
			 */
			'name'   => function()
			{
				throw new \FormloaderException('A name is needed in order to deal with this properly');
			},

			/**
			 * The _id of the form item... prefixed with "fs-" so that we can easily add
			 * mongoDB form items and be able to differentiate between the two
			 * @param  array   $f - current field array
			 * @return string  the _id of the field
			 */
			'_id'        => function($f)
			{
				return 'fs-'. $f['group'] . '-'.$f['name'];
			},

			'title' => function($f)
			{
				return ucwords(str_replace('_', ' ', $f['name']));
			},

			'attributes' => array(

				'id'    => function($f)
				{
					return Formloader_Bridge::unique_id($f);
				},

				'method' => 'POST',

				'action' => '{%%uri:://action%%}',

				'data'  => array()  // All data-attr will be filtered below
			),
			
			'__data' => function(&$f)
			{
				if ( ! empty($f['attributes']['data']))
				{
					foreach ($f['attributes']['data'] as $k => $v)
					{
						$f['attributes']["data-$k"] = $v;
					}
					$f['attributes']['data'] = '';
				}
				return '__remove__';
			},
			
			'api_action' => function($f)
			{
				return $f['group'] . '.' . $f['name'];
			},
			'form_open' => function($f)
			{
				return \Form::open(array_filter($f['attributes']), array('api_action' => $f['api_action']));
			},
			'form_close' => function()
			{
				return \Form::close();
			},

			'route_success' => '',
			'route_error'   => '',
			
			'fields' => array(),
			'__fields'  => function(&$f)
			{
				$f['fields'] = call_user_func("\\Formloader_Fields::_process_items", $f);
				return '__remove__';				
			},
			'has_fields' => function($f)
			{
				return (count($f['actions']) > 0);
			},
			
			'fieldsets'  => array(),
			'__fieldsets'  => function(&$f)
			{
				$f['fieldsets'] = call_user_func("\\Formloader_Fieldsets::_process_items", $f);
				return '__remove__';				
			},
			'has_fieldsets' => function($f)
			{
				return (count($f['fieldsets']) > 0);
			},
			
			'actions' => array(),
			'__actions'  => function(&$f)
			{
				$f['actions'] = call_user_func("\\Formloader_Actions::_process_items", $f);
				return '__remove__';				
			},
			
			'has_actions' => function($f)
			{
				return (count($f['actions']) > 0);
			},
			
			/**
			 * Default view for the action
			 * @var string
			 */
			'view'       => 'default.mustache',

			/**
			 * Resolves the view directory for the action
			 * @param  array $f - current action array
			 * @return string
			 */
			'view_dir'   => function($f)
			{
				return Formloader_Bridge::view_directory($f);
			},

			/**
			 * Path to the view relative to the "formloader/views" directory
			 * @param  array $f - current action array
			 * @return string
			 */			
			'view_path'  => function($f)
			{
				return $f['view_dir'].DS.$f['view'];
			},

			/**
			 * Output HTML for the action
			 * @param array
			 * @return string  rendered \View object
			 */
			'view_html' => function($f)
			{
				return \View::forge($f['view_path'], $f, false)->render();
			},
			
			/**
			 * All of the items that we need along with the form
			 * @param array $f - current form array
			 * @return array
			 */
			'form_packager' => function($f)
			{
				return array(
					'validation'    => \Formloader_Bridge::get('validations'),
					'selects'       => \Formloader_Bridge::get('selects'),
					'checks'        => \Formloader_Bridge::get('checks'),
					'option_calls'  => \Formloader_Bridge::get('option_calls'),
					'defaults'      => \Formloader_Bridge::get('defaults'),
					'route_success' => $f['route_success'],
					'route_error'   => $f['route_error'],
				);
			}
		);
	}
}