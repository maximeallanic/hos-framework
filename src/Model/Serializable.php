<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 20:24
 */

namespace Hos\Model;


use Sabre\Xml\XmlSerializable;
use Zend\Stdlib\JsonSerializable;

interface Serializable extends JsonSerializable, XmlSerializable
{

}