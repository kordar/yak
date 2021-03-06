<?php
namespace kordar\yak\assets\ace;

use yii\web\AssetBundle;

/**
 * Configuration for Ace Admin client script files
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@kordar/yak/resource/ace';

    public $css = [
        'yak.css'
    ];

    public $js = [
        'yak.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'kordar\yak\assets\ace\AceAsset',
        'kordar\yak\assets\ace\AceScriptAsset',
    ];
}