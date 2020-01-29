<?php

/**
 * 
 * rudimentary autoload function (currently only for static views)
 */
function GI_Autoload($class){    
    $fileURL = '';
    //split by hump notation
    $classStringChunks = preg_split('/(?=[A-Z])/',$class);
    
    $lastChunk = $classStringChunks[count($classStringChunks)-1];
    $lastChunkToLower = strtolower($lastChunk);
    
    $fileName = NULL;
    
    switch($lastChunkToLower){
        case 'view':
            if(empty($classStringChunks[0])){
                $firstChunk = strtolower($classStringChunks[1]);
                unset($classStringChunks[0]);
                unset($classStringChunks[1]);
            } else {
                $firstChunk = strtolower($classStringChunks[0]);
                unset($classStringChunks[0]);
            }

            $autoloadViews = array(
                'static',
                'mls'
            );
            if(in_array($firstChunk, $autoloadViews)){
                $fileDir = 'concrete/' . $firstChunk . '/view/';
                $fileName = $firstChunk . '_';
                $firstLowerCased = false;
                foreach($classStringChunks as $chunk){
                    if($firstLowerCased){
                        $fileName .= $chunk;
                    } else {
                        $fileName .= strtolower($chunk);
                        $firstLowerCased = true;
                    }
                }
                $fileName .= '.php';
                $fileURL = $fileDir . $fileName;
            }
            break;
        case 'controller':
            $lastChunkKey = array_search($lastChunk, $classStringChunks);
            unset($classStringChunks[$lastChunkKey]);

            $implodeChunks = strtolower(implode('', $classStringChunks));
            if(file_exists('controllers/' . $implodeChunks . $lastChunk . '.php')){
                $fileURL = 'controllers/' . $implodeChunks . $lastChunk . '.php';
                
            }
            break;
        default:
            if(file_exists('framework/super/' . FRMWK_SUPER_VER . '/GI_Autoload/GI_Autoload' . $lastChunk . '.php')){
                $maps = require_once('framework/super/' . FRMWK_SUPER_VER . '/GI_Autoload/GI_Autoload' . $lastChunk . '.php');
                $lastChunkKey = array_search($lastChunk, $classStringChunks);
                unset($classStringChunks[$lastChunkKey]);

                $implodeChunks = implode('', $classStringChunks);

                $mappedPath = $maps['default'];

                foreach($classStringChunks as $stringChunk){
                    if(isset($maps[$stringChunk])){
                        $mappedPath = $maps[$stringChunk];
                        break;
                    }
                }

                $fileURL = $mappedPath . $implodeChunks . '/' . $implodeChunks . $lastChunk . '.php';
            } else {
                $maps = require_once('framework/super/' . FRMWK_SUPER_VER . '/GI_Autoload/GI_AutoloadDefault.php');
                $implodeChunks = implode('', $classStringChunks);

                if (isset($maps['default'])) {
                    $mappedPath = $maps['default'];
                    foreach ($classStringChunks as $stringChunk) {
                        if (isset($maps[$stringChunk])) {
                            $mappedPath = $maps[$stringChunk];
                            break;
                        }
                    }

                    $fileURL = $mappedPath . $implodeChunks . '/' . $implodeChunks . '.php';
                }
            }
            break;
    }

    if (file_exists($fileURL)){
        require_once($fileURL);
    } elseif(!empty($fileName)) {
        $referenceViewFiles = scandir('concrete/static/view/reference');
        if(in_array($fileName, $referenceViewFiles)){
            require_once('concrete/static/view/reference/' . $fileName);
        }
    }
}

spl_autoload_register('GI_Autoload');
