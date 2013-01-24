<?php

class Dumper
{
    protected $lib;
    protected $config;
    protected $extension;
    
    function __construct($lib, $config)
    {
        $this->lib = $lib;
        $this->config = $config;
        if ($config['language'] == "php") {
            $this->extension = "php";
        } elseif ($config['language'] == "python") {
            $this->extension = "py";
        } elseif ($config['language'] == "perl") {
            $this->extension = "perl";
        } elseif ($config['language'] == "phparray") {
            $this->extension = "phparray";
        } elseif ($config['language'] == "ruby") {
            $this->extension = "rb";
        } else {
            throw new Exception("Language " . $config['language'] . " not supported.");
        }
    }
    
    public function dumpMethodData($method)
    {
        $methodData = $this->fetchMethodData("root_admin/${method}.html");
        print_r($methodData);
    }
    
    public function dumpMethod($method)
    {
        $methodData = $this->fetchMethodData("root_admin/${method}.html");
        $this->lib->render("method." . $this->extension . ".twig", array(
            "method" => $methodData,
            "config" => $this->config,
        ));
    }
    
    public function dumpLinks()
    {
        $links = Parser::getAllLinks($this->fetchTOC());
        foreach ($links as $link) {
            echo $link['url'] . " - " . $link['name'] ."\n";
        }
    }
    
    public function dumpClass()
    {
        $links = Parser::getAllLinks($this->fetchTOC());
        $methodsData = array();

        // walk through all links
        foreach ($links as $link) {
            $methodsData[] = $this->fetchMethodData($link['url']);
        }

        $this->lib->render("class." . $this->extension . ".twig", array(
            "methods" => $methodsData,
            "config" => $this->config,
        ));
    }
    
    private function fetchMethodData($path) {
        $url = $this->getRootUrl() . $path;
        $html = $this->lib->fetchHtml($url);
        return Parser::getMethodData($html);
    }
    
    private function fetchTOC() {
        // Download the API reference table of content 
        $url = $this->config['api_ref_toc_url'];
        return $this->lib->fetchHtml($url);
    }
    
    /**
     * Match the root of the table of content
     * for http://download.cloud.com/releases/2.2.0/api_2.2.4/TOC_User.html
     * the root is http://download.cloud.com/releases/2.2.0/api_2.2.4/
     */
    private function getRootUrl() {
        preg_match("/^(.*\/)[^\/]+$/", $this->config['api_ref_toc_url'], $matches);
        return $matches[1];
    }
}
