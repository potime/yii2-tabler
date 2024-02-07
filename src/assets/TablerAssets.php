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

namespace potime\tabler\assets;

class TablerAssets extends \yii\web\AssetBundle
{
    public $sourcePath = '@potime/tabler/dist';

    public $css = [
        'css/tabler.min.css',
    ];
    public $js = [
        'js/tabler.min.js',
    ];
}
