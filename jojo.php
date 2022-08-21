<?php

class Jojo {

    function letters() {
        $letters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        return $letters;
    }

    function name($max = false) {
        if (!$max) $max = mt_rand(4, 10);
        return Jojo::letter() . substr(md5(microtime()), 0, --$max);
    }

    function name_list($cnt = false) {
        if (!$cnt) $cnt = mt_rand(2,5);
        $ret = array();
        while(count($ret) < $cnt) {
            $ret[] = Jojo::name();
        }
        return implode(', ', $ret);
    }

    function letter() {
        $l = Jojo::letters();
        shuffle($l);
        return $l[0];
    }

    function int() {
        return mt_rand();
    }

    function string($len = false) {
        if (!$len) $len = mt_rand(10,50);
        $l = Jojo::letters();
        shuffle($l);
        $ret = '';
        $max = 0;
        while ($max < $len) {
            $ret .= Jojo::word() . ' ';
            $max = strlen($ret);
        }
        return '"' . substr($ret, 0, $len-2) . '"';
    }

    function word() {
        $l = Jojo::letters();
        shuffle($l);
        $len = 10;
        $l = array_slice($l, 0, mt_rand(1, $len-1));
        return implode($l);
    }

    function value() {
        $opt = array('string','int','null','undefined','true','false','NaN');
        shuffle($opt);
        $opt = $opt[0];

        if ($opt === 'int' || $opt === 'string') {
            return call_user_func(array('Jojo', $opt));
        }

        return $opt;
    }

    function value_obj() {
        $opt = array('{}','[]', 'function(){}');
        shuffle($opt);
        return $opt[0];
    }

    function value_list($cnt = false) {
        if (!$cnt) $cnt = mt_rand(2,5);
        $ret = array();
        while(count($ret) < $cnt) {
            $ret[] = Jojo::value();
        }
        return implode(', ', $ret);
    }

    function declaration() {
        $opt = array(
            'var name = value;',
            'var name = value_obj;',
            'var name_list;',
            'var name_list = value;',
            'name = value;',
            'name = value_obj;'
        );
        shuffle($opt);
        $opt = $opt[0];

        $find    = array('value_obj', 'name_list', 'name', 'value');
        $replace = array(Jojo::value_obj(), Jojo::name_list(), Jojo::name(), Jojo::value());
        return str_replace($find, $replace, $opt);
    }


    function declarations($cnt = false, $indent = '') {
        if (!$cnt) $cnt = mt_rand(2, 10);
        $ret = array();
        while(count($ret) < $cnt) {
            $ret[] = $indent . Jojo::declaration();
        }
        return implode("\n", $ret);
    }


    function aarray() {
        $opt = array(
            'var name = [];',
            'var name = [value_list];'
        );
        shuffle($opt);
        $opt = $opt[0];

        $find    = array('value_list');
        $replace = array(Jojo::value_list());

        return str_replace($find, $replace, $opt);
    }

    function sign() {
        $opt = array('+','-','%','/','*');
        shuffle($opt);
        return $opt[0];
    }

    function statement() {
        $opt = array(
            'name3 = name1 sign name2;',
            'name1 = value1 + value2;',
            'var name1 = name2 sign name3;',
            'var name1 = value1 + value2;',
            'name1 = name2(name3);',
            'name1 = name2(value1, value2);',
            'name1 = name2() sign name3();',
            'var name1 = name2(name3);',
            'var name1 = name2(value1, value2);',
            'var name1 = name2() sign name3();',
            'var name1 = name2() sign name3(value1);',
            'var name1 = name2(value1) sign name3();',
            'var name1 = name2(value2) sign name3(value1);',
            'var name1 = parseInt(name2, 10);',
            'var name1 = parseInt(value1, 10);',
            'var name1 = parseFloat(name2);',
            'var name1 = parseFloat(value1);',
            'var name1 = encodeURIComponent(name2);',
            'var name1 = decodeURIComponent(name2);',
            'var name1 = isNaN(name2);',
            'var name1 = isNaN(value1);',
        );
        shuffle($opt);
        $opt = $opt[0];

        $find = array('name1','value1','name2','value2','name3','sign','for','while','do');
        $replace = array(Jojo::name(),Jojo::value(),Jojo::name(),Jojo::value(),Jojo::name(),Jojo::sign());

        return str_replace($find, $replace, $opt);
    }

    function statements($cnt = false, $indent = '') {
        if (!$cnt) $cnt = mt_rand(2, 10);
        $ret = array();
        while(count($ret) < $cnt) {
            $ret[] = $indent . Jojo::statement();
        }
        return implode("\n", $ret);
    }

    function block($type = 'for', $indent = '') {
        $templates = array();
        $templates['for'] = "for (var name = 1; name < 2; name++) {\nstatements\n" . $indent . "}\n";
        $templates['while'] = "while (name < 1) {\nstatements\n" . $indent . "}\n";
        $templates['do'] = "do {\nstatements\n" . $indent . "} while (name < 1);\n";
        $templates['func'] = "function name(name_list){\ndeclarations\nstatements\n". $indent ."}\n";

        $tpl = $templates[$type];
        $find = array('name_list', 'name', '1', '2', 'statements', 'declarations');
        $replace = array(Jojo::name_list(), Jojo::name(), Jojo::int(), Jojo::int(), Jojo::statements(false, $indent . '  '), Jojo::declarations(false, $indent . '  '));

        return $indent . str_replace($find, $replace, $tpl);
    }

    function code($options = array()) {
        if (empty($options['size'])) {
            $options['size'] = 1;
        }
        if (empty($options['func'])) {
            $options['func'] = 'jojo';
        }

        $max = 1024 * $options['size'];

        $ret = "function ". $options['func'] ."(){";
        $work = true;
        while($work) {

            $new = "\n" . Jojo::declarations(false, '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }

            $new = "\n" . Jojo::statements(false, '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }

            $new = "\n" . Jojo::block('for', '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }

            $new = "\n" . Jojo::block('do', '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }

            $new = "\n" . Jojo::block('while', '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }

            $new = "\n" . Jojo::block('func', '  ');
            if (strlen($ret) + strlen($new) >= $max) {
                $work = false;
                break;
            } else {
                $ret .= $new;
            }
        }

        $ret .= "\n}";
        $rest = $max - strlen($ret);

        if ($rest === 1){
          $ret .= ' ';
        } else {
          $ret .= str_repeat('/', $rest);
        }
        return $ret;
    }
}

$size = 1024;
if ($_REQUEST['size'] && intval($_REQUEST['size']) > 0) {
    $size = intval($_REQUEST['size']);
}

$res = Jojo::code(array('size' => $size));

header('Content-Type: application/javascript');
header('Content-Length: ' . strlen($res));
echo $res;

?>
