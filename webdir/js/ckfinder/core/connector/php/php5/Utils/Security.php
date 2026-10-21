<?php
/*
 * CKFinder
 * ========
 * http://ckfinder.com
 * Copyright (C) 2007-2011, CKSource - Frederico Knabben. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */
if (!defined('IN_CKFINDER')) exit;

/**
 * @package CKFinder
 * @subpackage Utils
 * @copyright CKSource - Frederico Knabben
 */

/**
 * @package CKFinder
 * @subpackage Utils
 * @copyright CKSource - Frederico Knabben
 */
class CKFinder_Connector_Utils_Security
{

    /**
     * Magic quotes were removed in PHP 8; nothing to strip.
     * @access public
     */
    public function getRidOfMagicQuotes()
    {
    }

    /**
     * Strip quotes from variable
     *
     * @access public
     * @param mixed $var
     * @param int $depth current depth
     * @param int $howDeep maximum depth
     */
    public function stripQuotes(&$var, $depth=0, $howDeep=5)
    {
        if (is_array($var)) {
            if ($depth++<$howDeep) {
                foreach (array_keys($var) as $k) {
                    $this->stripQuotes($var[$k], $depth, $howDeep);
                }
            }
        } else {
            $var = stripslashes($var);
        }
    }
}
