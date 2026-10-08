<?php

namespace jeremykenedy\LaravelLogger\Support;

class UserAgentParser
{
    public function locale($locale)
    {
        if (class_exists('Locale')) {
            return \Locale::acceptFromHttp((string) $locale);
        }

        $languages = explode(',', (string) $locale);

        return $languages[0];
    }

    public function parse($agent): array
    {
        $agent = $agent === null ? ($_SERVER['HTTP_USER_AGENT'] ?? '') : $agent;
        $parts = $this->parts($agent);
        $fields = ['platform', 'type', 'renderer', 'browser', 'version'];
        if (count($parts) < count($fields)) {
            return array_fill_keys($fields, '-');
        }

        $details = array_combine($fields, array_slice($parts, 0, count($fields)));
        $details['version'] = $this->version($details['version']);

        return $this->mobileBrowser($this->browser($details));
    }

    private function parts($ua): array
    {
        $platforms = 'Windows|iPad|iPhone|Macintosh|Android|BlackBerry|Unix|Linux|X11|CrOS';

        $browsers = 'Firefox|Chrome|Opera';

        $browsers_v = 'Safari|Mobile'; // Mobile is mentioned in Android and BlackBerry UA's

        $engines = 'Gecko|Trident|Webkit|Presto';

        $pattern = "/((Mozilla)\/[\d\.]+|(Opera)\/[\d\.]+)\s\(.*?((MSIE)\s([\d\.]+).*?(Windows)|({$platforms})).*?\s.*?({$engines})[\/\s]+[\d\.]+(\;\srv\:([\d\.]+)|.*?).*?(Version[\/\s]([\d\.]+)(.*?({$browsers_v})|$)|(({$browsers})[\/\s]+([\d\.]+))|$).*/i";

        $replacement = '$7$8|$2$3|$9|${17}${15}$5$3|${18}${13}$6${11}';

        return explode('|', preg_replace($pattern, $replacement, $ua, PREG_PATTERN_ORDER));
    }

    private function version(string $version): string
    {
        return preg_match("/^[\d]+\.[\d]+(?:\.[\d]{0,2}$)?/", $version, $matches) ? $matches[0] : $version;
    }

    private function browser(array $details): array
    {
        $browser = strtolower($details['browser']);
        if (in_array($browser, ['msie', 'trident']) || ($browser === '' && strtolower($details['renderer']) === 'trident')) {
            $details['browser'] = 'Internet Explorer';
        }

        return $details;
    }

    private function mobileBrowser(array $details): array
    {
        if (in_array(strtolower($details['platform']), ['android', 'blackberry']) && in_array($details['browser'], ['Safari', 'Mobile', ''])) {
            $details['browser'] = $details['platform'].' mobile';
        }

        return $details;
    }
}
