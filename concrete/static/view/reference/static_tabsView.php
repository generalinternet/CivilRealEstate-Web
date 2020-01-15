<?php

class StaticTabsView extends GI_View {
    
    protected $exTexts = array(
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam lectus ligula, facilisis nec velit vel, imperdiet placerat ligula. Nulla facilisi. Sed sit amet aliquam mi, ac ornare lectus. In hac habitasse platea dictumst. Vestibulum vitae mi vitae mi bibendum lobortis. Morbi erat diam, ultrices sit amet mi quis, fringilla mattis magna. Nunc consectetur, ante sed dapibus auctor, odio turpis lobortis est, nec pellentesque ligula urna in sapien. Nulla lacinia aliquet interdum. Mauris feugiat elementum ligula, sed mollis orci facilisis vel. Donec ac hendrerit augue, sit amet semper erat. Suspendisse id metus id nisl egestas faucibus ac vel massa.',
        'Quisque eget condimentum libero. Phasellus ornare tortor a nibh tempus congue. Etiam commodo sed nibh ac elementum. Mauris placerat iaculis tortor in sodales. Etiam vel orci felis. Integer eget erat faucibus, ultrices dolor at, semper urna. Phasellus at velit tincidunt, dictum turpis nec, congue metus. Maecenas non eros ut enim elementum elementum. Ut elementum porta mauris, id vestibulum diam eleifend vel. Duis nec pulvinar orci, id pretium odio. Maecenas viverra vulputate dui sit amet aliquam. In eget sagittis diam, id gravida turpis. Aenean massa justo, molestie at iaculis vitae, consequat ut dolor. Phasellus efficitur pulvinar felis, sed commodo lorem aliquet at. Cras posuere congue accumsan. Pellentesque venenatis, felis eu sodales commodo, tortor mauris aliquam sapien, elementum vulputate diam metus nec tortor.',
        'Sed viverra lobortis interdum. Nulla interdum sagittis tortor sed consectetur. Duis vel urna dapibus, vulputate quam in, condimentum purus. Phasellus accumsan tempus odio. Sed rhoncus accumsan eros vel vehicula. Maecenas sed lorem et nulla aliquam egestas. Duis ut nibh at mauris malesuada interdum.',
        'Vivamus eleifend tristique leo tincidunt accumsan. Vivamus quis tempor eros, ac rhoncus orci. Sed aliquet, orci sed aliquam feugiat, tortor libero ultricies quam, quis placerat ipsum odio a libero. Ut mollis nisl ultricies urna ornare, ut venenatis arcu sodales. Etiam a odio orci. Pellentesque non quam quis ante placerat hendrerit. Donec ac tortor sagittis, pretium nunc et, luctus justo. Suspendisse vestibulum leo nisl, vitae malesuada magna egestas quis. Sed aliquet, ligula vel fermentum accumsan, ante urna tempor nisi, non convallis eros ex quis ex. Fusce porta sem eget tortor ultrices, eget aliquet quam aliquet. Nulla eget est vel lorem viverra auctor non quis ante. Suspendisse potenti.',
        'Vivamus sit amet laoreet nisi, et sagittis diam. Duis odio mauris, tincidunt vel efficitur a, egestas sit amet purus. Praesent egestas molestie mi quis lacinia. Nunc congue ex ut nisl efficitur, in accumsan quam feugiat. Aenean aliquam dapibus rhoncus. Quisque ut lectus et turpis iaculis laoreet. Fusce id diam velit. Sed efficitur mattis eros sed maximus. Aliquam erat volutpat. Mauris magna orci, sagittis eu mi eget, vestibulum condimentum nunc. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce volutpat lacus sed orci cursus, ut pulvinar leo egestas. Aliquam sit amet nibh ante. Vestibulum sit amet rhoncus erat. Praesent nunc sem, imperdiet at eros at, imperdiet interdum ligula. Pellentesque finibus tellus odio.',
        'Mauris laoreet, nunc vitae venenatis consequat, eros ipsum consequat tellus, quis aliquam tortor massa in mauris. Mauris id nisi eget diam malesuada ornare quis ut justo. Praesent sodales nisi diam, vitae sagittis erat vulputate et. Duis id nibh lectus. Nulla ipsum massa, rhoncus vitae urna at, dignissim vehicula massa. Pellentesque feugiat, urna porta facilisis consectetur, ex nunc egestas dolor, a consectetur dolor erat at magna. Curabitur in convallis augue.',
        'Donec nec enim ac dui lobortis pretium id non ex. Phasellus pretium dictum magna, vel auctor nisi bibendum vitae. Etiam ac posuere lectus. Suspendisse vel risus urna. Vivamus ac purus velit. Curabitur malesuada iaculis elit sit amet ultricies. Integer ut pulvinar diam. Morbi eleifend felis a mauris iaculis, eleifend congue eros molestie. Phasellus eget venenatis risus. Duis id leo justo. Ut egestas dui eros, vel fermentum sem volutpat eu. Vestibulum dapibus ante eros, ac convallis odio gravida eu.'
    );
    
    protected $curTextCount = 1;
    
    public function __construct() {
        $this->addSiteTitle('Tabs');
        parent::__construct();
    }
    
    public function buildView(){
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_body">');
        
        $tabView = new GenericTabView('Tab 1');
        $this->addTabViewContent($tabView);
        $tabView2 = new GenericTabView('Tab 2');
        $this->addTabViewContent($tabView2);
        $tabView3 = new GenericTabView('Tab 3');
        $this->addTabViewContent($tabView3);
        $tabView4 = new GenericTabView('Tab 4');
        $this->addTabViewContent($tabView4);
        $tabView5 = new GenericTabView('Tab 5');
        $this->addTabViewContent($tabView5);
        $tabView6 = new GenericTabView('Tab 6');
        $this->addTabViewContent($tabView6);
        $tabView7 = new GenericTabView('Tab 7');
        $this->addTabViewContent($tabView7);
        $tabView2->setCurrent(true);
        $tabWrap = new GenericTabWrapView(array(
            $tabView
        ));
        $this->addHTML($tabWrap->getHTMLView());

        $tabWrap2 = new GenericTabWrapView(array(
            $tabView,
            $tabView2
        ));
        $this->addHTML($tabWrap2->getHTMLView());

        $tabWrap3 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3
        ));
        $this->addHTML($tabWrap3->getHTMLView());

        $tabWrap4 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3,
            $tabView4
        ));
        $this->addHTML($tabWrap4->getHTMLView());

        $tabWrap5 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3,
            $tabView4,
            $tabView5
        ));
        $this->addHTML($tabWrap5->getHTMLView());
        
        $tabWrap6 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3,
            $tabView4,
            $tabView5,
            $tabView6
        ));
        $this->addHTML($tabWrap6->getHTMLView());
        
        $tabView->resetHTML();
        $tabView2->resetHTML();
        $tabView3->resetHTML();
        $tabView4->resetHTML();
        $tabView5->resetHTML();
        $tabView6->resetHTML();
        
        $tabWrap7 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3,
            $tabView4,
            $tabView5,
            $tabView6,
            $tabView7
        ));
        $this->addHTML($tabWrap7->getHTMLView());
        
        $tabWrap8 = new GenericTabWrapView(array(
            $tabView,
            $tabView2,
            $tabView3,
            $tabView4,
            $tabView5,
            $tabView6,
            $tabView7
        ));
        $tabWrap8->setSideLabelsOnLeft(false);
        $this->addHTML($tabWrap8->getHTMLView());
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function addTabViewContent(GenericTabView $tabView){
        $tabTitle = $tabView->getTabTitle();
        $tabView->addHTML('<h2 class="main_head">' . $tabTitle . ' Content</h2>');
        $tabView->addHTML('<p>This is the content area for <b>' . $tabTitle . '</b>.</p>');
        
        for($i=1; $i<=$this->curTextCount; $i++){
            $textKey = $i-1;
            if(isset($this->exTexts[$textKey])){
                $tabText = $this->exTexts[$textKey];
            } else {
                $tabText = $this->exTexts[rand(0,count($this->exTexts) - 1)];
            }
            $tabView->addHTML('<p>' . $tabText . '</p>');
        }
        $this->curTextCount++;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
