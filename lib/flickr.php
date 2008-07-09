<?php
class Flickr
{
    var $cache;

    function Flickr()
    {
        $this->cache = '/tmp/flickr.cache';
        $this->user_nsid = '12377165@N00';
    }

    function refreshCache()
    {
        $url = 'http://api.flickr.com/services/feeds/photos_public.gne';
        $url .= "?id={$this->user_nsid}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);

        $images = $this->parse($result);

        $fh = fopen($this->cache, 'w');
        fwrite($fh, serialize($images));
        fclose($fh);

        return $images;
    }

    function parse($xml)
    {
        $images = array();
        if (function_exists('domxml_open_mem'))
        {
            $dom = domxml_open_mem($xml);
            $entries = $dom->get_elements_by_tagname('item');
            foreach ($entries as $entry)
            {
                $titles = $entry->get_elements_by_tagname('title');
                $title = $titles[0]->get_content();
                foreach ($entry->get_elements_by_tagname('link') as $link)
                {
                    $rel = $link->get_attribute('rel');
                    $href = $link->get_attribute('href');
                    if ($rel == 'alternate')
                    {
                        $url = $href;
                    }
                    if ($rel == 'enclosure')
                    {
                        $image = $href;
                    }
                }

                // cache a thumbnail of the image
                $images[] = array(
                    'title' => (string)$title,
                    'url' => (string)$url,
                    'thumbnail' => "/images/thumbs.php?url=".urlencode($image)
                );
            }
        }
        elseif (function_exists('simplexml_load_string'))
        {
            $xml = simplexml_load_string($xml);
            foreach ($xml->item as $item)
            {
                $images[] = array(
                    'title' => (string) $item->title,
                    'url' => (string) $item->link,
                    'thumbnail' => (string) $item->thumbnail
                );
            }
        }
        return $images;
    }

    function recentPhotos()
    {
        $cached = file_exists($this->cache);
        if (!$cached)
        {
            $images = $this->refreshCache();
        }
        else
        {
            $images = unserialize(file_get_contents($this->cache));
        }
        return $images;
    }
}

function flickr_grid()
{
    $flickr =& new Flickr();
    $images = $flickr->recentPhotos();
    include minim()->template('_flickr');
}
