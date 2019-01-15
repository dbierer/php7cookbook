<?php
return [
    // NOTE: using Unicode Escape Point Syntax to position cedilla and tilda
    'languages' => ['de' => 'Deutsch','en' => 'English','es' => "Espan\u{0303}ol",'fr' => "Franc\u{0327}ais"],
    'city' => [
        'Bangkok' => 'Bangkok',
        'Amsterdam' => 'Amsterdam',
        'Chicago' => 'Chicago',
    ],
    'people' => [
        'Napoleon' => 'Napoleon',
        'Jane Austen' => 'Jane_Austen',
        'Leonardo da Vinci' => 'Leonardo_da_Vinci',
    ],
    'callback' => [
        'url'    => function ($item) { 
                        return sprintf('https://%s.wikipedia.org/wiki/%s', $_SESSION['lang'], $item); 
                    },
        'option' => function ($key, $value) { 
                        return sprintf('<option value="%s">%s</option>', $key, $value); 
                    },
        'nav'    => function ($code, $name) { 
                        return sprintf('<li class="page-scroll"><a href="?langCode=' . $code . '" class="scroll">' . $name . '</a></li>'); 
                    },
        'top-menu' => function() {
                        $output   = '';
                        $langs    = $this->config['languages'];
                        $callback = $this->config['callback']['nav'];
                        foreach ($langs as $code => $name) $output .= $callback($code, $name);
                        return $output;
                    },
        'people' => function ($name, $url, $img) {
                        return '<div class="col-md-3 team-member">'
							. '	<div class="team-member-info">'
							. '		<img class="member-pic" src="' . $img . '" title="' . $name . '" />'
							. '		<h5><a href="' . $url . '">' . $name . '</a></h5>'
							. '		<label class="team-member-caption text-center">'
							. '			<p>&nbsp;</p>'
							. '			<ul>'
							. '				<li><a class="t-twitter" href="#"><span> </span></a></li>'
							. '				<li><a class="t-facebook" href="#"><span> </span></a></li>'
							. '				<li><a class="t-googleplus" href="#"><span> </span></a></li>'
							. '				<div class="clearfix"> </div>'
							. '			</ul>'
							. '		</label>'
							. '</div>'
							. '</div><!--- end-team-member --->'; 
                    },            
        'people-div' => function () {
                        $output = '';
                        $people = $this->config['people'];
                        $callback = $this->config['callback']['people'];
                        foreach ($people as $name => $code) {
                            $url = $this->config['callback']['url']($code);
                            $src = $this->vac->getAttribute($url, 'src');
                            $img = '';
                            foreach ($src as $jpg) {
                                if (stripos($jpg, 'jpg')) {
                                    $img = $jpg;
                                    break;
                                }
                            }
                            $output .= $callback($name, $url, $img);
                        }
                        return $output;
                    },
        'city' => function ($name, $text, $img) {
                        return '<li>'
                            . '<img src="/images/slide.jpg" alt="">'
                            . '<div class="caption text-center">'
                            . '<div class="slide-text-info">'
                            . '    <h1>' . $name . '</h1>'
                            . '    <div class="slide-text">'
                            . $text
                            . '    </div>'
                            . '    <div class="clearfix"> </div>'
                            . '    <div class="big-btns">'
                            . '        <a class="view" href="#"><label> </label>View</a>'
                            . '    </div>'
                            . '</div>'
                            . '</div>'
                            . '</li>';
                        },
        'city-div' => function () {
                        $output = '';
                        $cities = $this->config['city'];
                        $callback = $this->config['callback']['city'];
                        foreach ($cities as $name => $code) {
                            $url  = $this->config['callback']['url']($code);
                            $tags = $this->vac->getTags($url, 'p');
                            $text = $tags[0]['value'] ?? $tags[1]['value'] ?? $tags[2]['value'];
                            $output .= $callback($name, $text, '');
                        }
                        return $output;
                    },
    ],
];
