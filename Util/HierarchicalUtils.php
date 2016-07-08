<?php

namespace ITE\FormBundle\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class HierarchicalUtils
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class HierarchicalUtils
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function isHierarchicalRequest(Request $request)
    {
        return $request->headers->has('X-SF-Hierarchical');
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public static function getOriginators(Request $request)
    {
        $originators = $request->headers->get('X-SF-Hierarchical-Originator');
        if (null === $originators) {
            return null;
        }

        return explode(',', $originators);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public static function getSingleOriginator(Request $request)
    {
        $originators = self::getOriginators($request);
        if (null === $originators) {
            return null;
        }

        $originator = reset($array);

        return false !== $originator ? $originator : null;
    }
}
