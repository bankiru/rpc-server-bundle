<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.03.2016
 * Time: 10:27
 */
namespace Bankiru\Api\Rpc\Controller;

interface ControllerNameParser
{
    /**
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string in the class::method notation
     *
     * @throws \InvalidArgumentException when the specified bundle is not enabled
     *                                   or the controller cannot be found
     */
    public function parse($controller);

    /**
     * Converts a class::method notation to a short one (a:b:c).
     *
     * @param string $controller A string in the class::method notation
     *
     * @return string A short notation controller (a:b:c)
     *
     * @throws \InvalidArgumentException when the controller is not valid or cannot be found in any bundle
     */
    public function build($controller);
}
