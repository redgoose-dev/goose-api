<?php
namespace Core;


class Install {

	public function __construct()
	{
		//
	}

	/**
	 * Check installed
	 *
	 * @return boolean
	 */
	public function check()
	{
		// TODO: check exist `data/`
		// TODO: check exist `data/upload/`
		// TODO: check permission `data/`
		// TODO: check permission `data/upload/`
		return true;
	}

	public function form()
	{
		echo "install form";
	}

}