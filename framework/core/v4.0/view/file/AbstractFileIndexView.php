<?php

abstract class AbstractFileIndexView extends GI_View {

    public function __construct() {
        parent::__construct();
        $this->buildView();
    }

    protected function buildView() {
        $this->addContent('<h1>Files</h1>');
    }

}
