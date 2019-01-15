<?php
// vacuuming a website
namespace Application\Mvc;

class City
{
    public function render($cities, $dispatch)
    {
        // get main text
        $output = '';
        foreach ($cities as list($cityName, $cityCode)) {
            $url    = $dispatch->getUrl($cityCode);
            echo '<br>' . __METHOD__ . ':' . $url . '<br>';
            /*
            //$pTags  = $dispatch->vac->getTags($url, 'p');
            $img    = '';
            $text   = '';
            $output .= '<li>';
            $output .= '<img src="' . $img . '" alt="">';
            $output .= '<div class="caption text-center">';
            $output .= '<div class="slide-text-info">';
            $output .= '    <h1>' . $name . '</h1>';
            $output .= '    <div class="slide-text">';
            $output .= $text;
            $output .= '<br>' . $url . '<br>';
            //$output .= var_export($pTags, TRUE);
            $output .= '    </div>';
            $output .= '    <div class="clearfix"> </div>';
            $output .= '    <div class="big-btns">';
            $output .= '        <a class="view" href="#"><label> </label>View</a>';
            $output .= '    </div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</li>';
            */
        }
        return $output;
    }
}
