<?php

namespace Larapress\Auth\Rendering;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larapress\CRUDRender\Base\ICRUDBladeViewProvider;
use Larapress\Dashboard\Rendering\JSONCRUDViewProvider;

class VueCRUDViewProvider extends JSONCRUDViewProvider implements ICRUDBladeViewProvider
{

    /**
     * Show the list of the resource.
     *
     * @param Request $request
     * @return Response|string
     */
    public function getListViewName(Request $request)
    {
        return self::getThemeViewName('vue.app');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return Response|string
     */
    public function getCreateViewName(Request $request)
    {
        return self::getThemeViewName('vue.app');
    }

    /**
     * Handle incoming create

     * @param Request $request
     * @param $object
     *
     * @return Response|string
     */
    public function getPostCreateViewName(Request $request, $object)
    {
        return self::getThemeViewName('vue.app');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @return Response|string
     */
    function getUpdateViewName(Request $request)
    {
        return self::getThemeViewName('vue.app');
    }

    /**
     * Handle incoming edit
     *
     * @param         $object
     * @param Request $request
     *
     * @return Response|string
     */
    function getPostUpdateViewName(Request $request, $object)
    {
        return self::getThemeViewName('vue.app');
    }
    /**
     * @param Request $request
     * @return string
     */
    function getWidgetsViewName(Request $request)
    {
        return self::getThemeViewName('vue.app');
    }

    /**
     * @param Request $request
     * @return null|string
     */
    function getPostWidgetsViewName(Request $request)
    {
        return self::getThemeViewName('vue.app');
    }


    public static function getThemeViewName($viewName)
    {
        $theme = config('larapress.auth.theme.blade.name');
        $package = config('larapress.auth.theme.blade.namespace');
        $view = null;
        if (isset($theme) && !Str::startsWith($viewName, 'themes.'.$theme)) {
            $view = (isset($package) ? $package.'::':'').'themes.'.$theme.'.'.$viewName;
        } else {
            $view = (isset($package) ? $package.'::':'').$viewName;
        }
        return $view;
    }
}
