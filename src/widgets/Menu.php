<?php
/**
 * This file is part of the yii2-tabler
 *
 * Author: iVan.k <ivan@potime.com>
 * Copyright (c): 2010-2023 iVan.k, All rights reserved
 * Version: 1.0.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace potime\tabler\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Menu displays a multi-level menu using nested HTML lists.
 *
 * The main property of Menu is [[items]], which specifies the possible items in the menu.
 * A menu item can contain sub-items which specify the sub-menu under that menu item.
 *
 * Menu checks the current route and request parameters to toggle certain menu items
 * with active state.
 *
 * Note that Menu only renders the HTML tags about the menu. It does do any styling.
 * You are responsible to provide CSS styles to make it look like a real menu.
 *
 * The following example shows how to use Menu:
 *
 * ```php
 * echo Menu::widget([
 *     'items' => [
 *         // Important: you need to specify url as 'controller/action',
 *         // not just as 'controller' even if default action is used.
 *         ['label' => 'Home', 'url' => ['site/index']],
 *         // 'Products' menu item will be selected as long as the route is 'product/index'
 *         ['label' => 'Products', 'url' => ['product/index'], 'items' => [
 *             ['label' => 'New Arrivals', 'url' => ['product/index']],
 *             ['label' => 'Most Popular', 'url' => ['product/index']],
 *         ]],
 *         ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
 *     ],
 * ]);
 * ```
 */
class Menu extends \yii\widgets\Menu
{
    /**
     * @var string the template used to render the body of a menu which is a link.
     * In this template, the token `{url}` will be replaced with the corresponding link URL;
     * while `{label}` will be replaced with the link text.
     * This property will be overridden by the `template` option set in individual menu items via [[items]].
     */
    public $linkTemplate = '<a class="nav-link {active}" href="{url}" {target}>{label}</a>';

    /**
     * {@inheritdoc}
     * Styles all labels of items on sidebar by Tabler
     */
    public $labelTemplate = '{label} {submenu} {badge}';

    /**
     * @var string the template used to render a list of sub-menus.
     * in this template, the token `{items}` will be replaced with the rendered sub-menu items.
     */
    public $submenuTemplate = "\n<ul id='{id}' class='nav nav-pills collapse {active}'>\n{items}\n</ul>\n";

    /**
     * @var string used to render the template as the parent link of the submenu.
     * In this template, the token `{url}` will be replaced with the corresponding link URL;
     * while `{label}` will be replaced with the link text.
     * This property will be overridden by the `template` option set in individual menu items via [[items]].
     */
    public $submenuLinksTemplate = '<a class="nav-link collapsed" href="{url}" data-bs-toggle="collapse" aria-expanded="false">{label}</a>';

    /**
     * @var array list of HTML attributes shared by all menu [[items]]. If any individual menu item
     * specifies its `options`, it will be merged with this property before being used to generate the HTML
     * attributes for the menu item tag. The following special options are recognized:
     *
     * - tag: string, defaults to "li", the tag name of the item container tags.
     *   Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see Html::renderTagAttributes for details on how attributes are being rendered.
     */
    public $itemOptions = ['class' => 'nav-item'];

    /**
     * @var bool whether to activate parent menu items when one of the corresponding child menu items is active.
     * The activated parent menu items will also have its CSS classes appended with [[activeCssClass]].
     */
    public $activateParents = true;

    /**
     * @var array the HTML attributes for the menu's container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "ul", the tag name of the item container tags. Set to false to disable container tag.
     *   See also [[\yii\helpers\Html::tag()]].
     *
     * @see Html::renderTagAttributes for details on how attributes are being rendered.
     */
    public $options = [
        'class' => 'nav nav-pills nav-vertical navbar-nav pt-lg-3',
        'role' => 'menu',
        'data-accordion' => 'false'
    ];


    private $_id = 0;

    /**
     * Recursively renders the menu items (without the container tag).
     * @param array $items the menu items to be rendered recursively
     * @return string
     * @throws \Exception
     */
    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));

            if (isset($item['items'])) {
                $item['id'] = $item['id'] ?? 'm' . $this->_id;
                $this->_id++;
            }

            $tag = ArrayHelper::remove($options, 'tag', 'li');

            $menu = $this->renderItem($item);
            if (!empty($item['items'])) {
                $submenuTemplate = ArrayHelper::getValue($item, 'submenuTemplate', $this->submenuTemplate);
                $menu .= strtr($submenuTemplate, [
                    '{id}' => $item['id'],
                    '{items}' => $this->renderItems($item['items']),
                    '{active}' => $item['active'] ? 'show' : '',
                ]);
            }

            $lines[] = Html::tag($tag, $menu, $options);
        }

        return implode("\n", $lines);
    }

    /**
     * Renders the content of a menu item.
     * Note that the container and the sub-menus are not rendered here.
     * @param array $item the menu item to be rendered. Please refer to [[items]] to see what data might be in the item.
     * @return string the rendering result
     * @throws \Exception
     */
    protected function renderItem($item)
    {
        if (isset($item['header']) && $item['header']) {
            return $item['label'];
        }

        $submenu = '';
        if (isset($item['items'])) {
            $submenu = '<span class="nav-link-toggle"></span>';
        }

        $badge = '';
        if (isset($item['badge']) && $item['badge'] && isset($item['badge']['message'])) {
            $badgeType = $item['badge']['badgeType'] ?? 'info';
            $badge = '<span class="right badge badge-' . $badgeType . '">' . $item['badge']['message'] . '</span>';
        }

        $template = ArrayHelper::getValue($item, 'template', (isset($item['items']) ? $this->submenuLinksTemplate : $this->linkTemplate));
        return strtr($template, [
            '{label}' => strtr($this->labelTemplate, [
                '{label}' => $item['label'],
                '{badge}' => $badge,
                '{submenu}' => $submenu
            ]),
            '{url}' => isset($item['items']) ? '#' . $item['id'] : (isset($item['url']) ? Url::to($item['url']) : '#'),
            '{active}' => $item['active'] ? $this->activeCssClass : '',
            '{target}' => isset($item['target']) ? 'target="' . $item['target'] . '"' : ''
        ]);
    }
}