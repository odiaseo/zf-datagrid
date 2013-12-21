<?php
namespace SynergyDataGrid\Grid\GridType;

/*
 * This file is part of the Synergy package.
 *
 * (c) Pele Odiase <info@rhemastudio.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Pele Odiase
 * @license http://opensource.org/licenses/BSD-3-Clause
 *
 */

use SynergyDataGrid\Grid\Base;

class SubGrid extends Base
{
    const GRID_PADDING  = 20;
    const WRAPPER_CLASS = 'subgrid-data';

    public $name = array();
    public $width = array();
    public $align = array();
    public $params = array();

    protected $_entity;
    protected $_service;

    public function __construct($entity, $service, $entityManager)
    {
        $this->_entity  = $entity;
        $this->_service = $service;
        $this->setEntityManager($entityManager);
    }

    public function getService()
    {
        return $this->_service;
    }

    public function setEntityManager($entityManager)
    {
        $this->_entityManager = $entityManager;

        return $this;
    }

    public function getEntityManager()
    {
        return $this->_entityManager;
    }

}