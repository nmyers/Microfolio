<?php
/* 
 * This file is automatically included
 * 
 */

function set_thumbnails($project,$mode=1,$width=100,$height=0,$pos=5,$bkg='fff') {
    $base_uri = _set_uri($project,$mode,$width,$height,$pos,$bkg);
    foreach($project['gallery']->find('div.image img') as $e) $e->src = makeUrl ($base_uri.$project['name'].'/'.$e->src);
}


function set_images($project,$mode=0,$width=800,$height=0,$pos=5,$bkg='fff') {
    $base_uri = _set_uri($project,$mode,$width,$height,$pos,$bkg);
    foreach($project['gallery']->find('div.image a') as $e) $e->href = makeUrl ($base_uri.$project['name'].'/'.$e->href);
}


function _set_uri($project,$mode=1,$width=100,$height=0,$pos=5,$bkg='fff') {
    //original
    if ($mode==0) $base_uri = 'image/';
    //resize
    if ($mode==1) $base_uri = sprintf('image/%s/%s/%s/',$mode,$width,$height);
    //crop
    if ($mode==2) $base_uri = sprintf('image/%s/%s/%s/%s/',$mode,$width,$height,$pos);
    //reframe
    if ($mode==3) $base_uri = sprintf('image/%s/%s/%s/%s/%s/',$mode,$width,$height,$pos,$bkg);
    
    return $base_uri;
}