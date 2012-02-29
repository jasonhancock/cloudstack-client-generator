<?php

class Parser
{
    /**
     * Get all the hyperlinks from the HTML document given
     * and return them in an array
     */
    public static function getAllLinks($html)
    {
        $links = array();

        foreach($html->find('a') as $a) {
            $url = $a->href;

            // Links black list
            // Exclude page that are not method documentation
            // You may need to edit the rules if the documentation has changed
            // if (substr($url, 0, 5) != "user/" || substr($url, 0, 8) == "user/2.2") {
            //if (substr($url, 0, 13) != "global_admin/" || substr($url, 0, 16) == "global_admin/2.2") {
            if (substr($url, 0, 11) != "root_admin/") {
                continue;
            }

            $links[] = array(
                'url' => $url,
                'name' => trim($a->plaintext),
            );
        }

        return $links;
    }

    /**
     * Fetch the data of the reference page of one method
     * and returns it in an array
     */
    public static function getMethodData($html)
    {
        // The name of the method is in the first and only one h1
        $title = $html->find('h1', 0);
        if ($title == null) {
            die("Error getting $url");
        }
        $data = array(
            'name' => trim($title->plaintext),
            // The description of the method is in the next block
            'description' => html_entity_decode(trim($title->next_sibling()->plaintext), ENT_QUOTES),
        );

        // The arguments of the method are all in the first table
        $params_table = $html->find('table', 0);

        // then, capturing the 3 cells of each lines :
        // parameter name, description of the paramter and wether if it is required or not
        foreach($params_table->find('tr') as $tr) {
            $name = trim($tr->find('td', 0)->plaintext);
            if ($name != "Parameter Name") {
                $data['params'][] = array(
                    "name" => $name,
                    "description" => html_entity_decode(trim($tr->find('td', 1)->plaintext), ENT_QUOTES),
                    "required" => trim($tr->find('td', 2)->plaintext),
                );
            }
        }

        // All the methods starting with list have a additionnal parameter
        // for pagination, not required
        if (substr($data['name'], 0, 4) == "list") {
            $data['params'][] = array(
                "name" => "page",
                "description" => "Pagination",
                "required" => "false",
            );
        }

        return $data;
    }
}
