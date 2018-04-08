<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

trait UserAgentDetails
{
    /**
     * Get the user's agents details.
     *
     * @param $ua
     *
     * @return array
     */
    public static function details($ua)
    {
        $ua = is_null($ua) ? $_SERVER['HTTP_USER_AGENT'] : $ua;
        // Enumerate all common platforms, this is usually placed in braces (order is important! First come first serve..)
        $platforms = 'Windows|iPad|iPhone|Macintosh|Android|BlackBerry|Unix|Linux';

        // All browsers except MSIE/Trident and..
        // NOT for browsers that use this syntax: Version/0.xx Browsername
        $browsers = 'Firefox|Chrome|Opera';

        // Specifically for browsers that use this syntax: Version/0.xx Browername
        $browsers_v = 'Safari|Mobile'; // Mobile is mentioned in Android and BlackBerry UA's

        // Fill in your most common engines..
        $engines = 'Gecko|Trident|Webkit|Presto';

        // Regex the crap out of the user agent, making multiple selections and..
        $regex_pat = "/((Mozilla)\/[\d\.]+|(Opera)\/[\d\.]+)\s\(.*?((MSIE)\s([\d\.]+).*?(Windows)|({$platforms})).*?\s.*?({$engines})[\/\s]+[\d\.]+(\;\srv\:([\d\.]+)|.*?).*?(Version[\/\s]([\d\.]+)(.*?({$browsers_v})|$)|(({$browsers})[\/\s]+([\d\.]+))|$).*/i";

        // .. placing them in this order, delimited by |
        $replace_pat = '$7$8|$2$3|$9|${17}${15}$5$3|${18}${13}$6${11}';

        // Run the preg_replace .. and explode on |
        $ua_array = explode('|', preg_replace($regex_pat, $replace_pat, $ua, PREG_PATTERN_ORDER));

        if (count($ua_array) > 1) {
            $return['platform'] = $ua_array[0];  // Windows / iPad / MacOS / BlackBerry
            $return['type'] = $ua_array[1];  // Mozilla / Opera etc.
            $return['renderer'] = $ua_array[2];  // WebKit / Presto / Trident / Gecko etc.
            $return['browser'] = $ua_array[3];  // Chrome / Safari / MSIE / Firefox

            /*
               Not necessary but this will filter out Chromes ridiculously long version
               numbers 31.0.1234.122 becomes 31.0, while a "normal" 3 digit version number
               like 10.2.1 would stay 10.2.1, 11.0 stays 11.0. Non-match stays what it is.
            */
            if (preg_match("/^[\d]+\.[\d]+(?:\.[\d]{0,2}$)?/", $ua_array[4], $matches)) {
                $return['version'] = $matches[0];
            } else {
                $return['version'] = $ua_array[4];
            }
        } else {
            return false;
        }

        // Replace some browsernames e.g. MSIE -> Internet Explorer
        switch (strtolower($return['browser'])) {
            case 'msie':
            case 'trident':
                $return['browser'] = 'Internet Explorer';
                break;
            case '': // IE 11 is a steamy turd (thanks Microsoft...)
                if (strtolower($return['renderer']) == 'trident') {
                    $return['browser'] = 'Internet Explorer';
                }
            break;
        }

        switch (strtolower($return['platform'])) {
            case 'android':    // These browsers claim to be Safari but are BB Mobile
            case 'blackberry': // and Android Mobile
                if ($return['browser'] == 'Safari' || $return['browser'] == 'Mobile' || $return['browser'] == '') {
                    $return['browser'] = "{$return['platform']} mobile";
                }
                break;
        }

        return $return;
    }

    /**
     * Return the locales language from PHP's Local
     * http://php.net/manual/en/class.locale.php
     * http://php.net/manual/en/locale.acceptfromhttp.php.
     *
     * @param string $locale
     *
     * @return string (Example: "en_US")
     */
    public static function localeLang($locale)
    {
        return \Locale::acceptFromHttp($locale);
    }
}
