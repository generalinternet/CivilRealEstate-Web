<?php
/**
 * Description of AbstractFileAvatarPlaceholderView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class AbstractFileAvatarPlaceholderView extends GI_View{
    
    /** @var AbstractUser */
    protected $user = NULL;
    protected $definedWidth = '44px';
    protected $definedHeight = '44px';
    protected $modelNum = 1;
    protected $avatarColours = array(
        'bg' => '#87c6db',
        'hair' => '#543a29',
        'skin' => '#fc9',
        'shirt' => '#5f3b75',
        'misc' => '#2b1b10'
    );
    
    public function __construct(AbstractUser $user = NULL) {
        $this->user = $user;
        parent::__construct();
    }
    
    public function setSize($width, $height){
        $this->definedWidth = $width;
        $this->definedHeight = $height;
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function getUserId(){
        if($this->user){
            return $this->user->getId();
        }
        return '0';
    }
    
    public function setModelNum($modelNum){
        $this->modelNum = $modelNum;
        return $this;
    }
    
    protected function getModelNum(){
        return $this->modelNum;
    }
    
    protected function getSVGClass(){
        return 'avatar_user_' . $this->getUserId();
    }
    
    public function randomize(){
        $this->modelNum = rand(1,4);
        foreach($this->avatarColours as $prop => $colour){
            $randomColour = GI_Colour::getRandomColour();
            $this->avatarColours[$prop] = '#' . $randomColour;
        }
    }
    
    protected function addStyleString(){
        $svgClass = $this->getSVGClass();
        $this->addHTML('<style>');
        $this->addHTML('.' . $svgClass . ' svg .avatar-bg{fill: ' . $this->avatarColours['bg'] . ';}');
        $this->addHTML('.' . $svgClass . ' svg .avatar-hair{fill: ' . $this->avatarColours['hair'] . ';}');
        $this->addHTML('.' . $svgClass . ' svg .avatar-skin{fill: ' . $this->avatarColours['skin'] . ';}');
        $this->addHTML('.' . $svgClass . ' svg .avatar-shirt{fill: ' . $this->avatarColours['shirt'] . ';}');
        $this->addHTML('.' . $svgClass . ' svg .avatar-misc{fill: ' . $this->avatarColours['misc'] . ';}');
        $this->addHTML('</style>');
    }
    
    protected function buildView(){
        $this->addStyleString();
        $this->addHTML('<span class="avatar_placeholder inline_block">');
        $this->addHTML(GI_StringUtils::getSVGAvatar($this->getModelNum(), $this->definedWidth, $this->definedHeight, $this->getSVGClass()));
        $this->addHTML('</span>');
    }
    
}
