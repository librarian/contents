<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * MaxSite CMS
 * (с) http://max-3000.com/
 */


# функция автоподключения плагина
function contents_autoload($args = array())
{
	mso_hook_add( 'head', 'contents_head');
	mso_hook_add( 'content_out', 'contents_content'); # хук на вывод контента после обработки всех тэгов
    mso_register_widget('contents_widget', t('contents') ); # регистрируем виджет
}

function contents_uninstall($args = array())
{
    mso_delete_option('plugin_contents', 'plugins' ); // удалим созданные опции
    return $args;
}


function contents_mso_options()
{

    mso_admin_plugin_options('plugin_contents', 'plugins',
        array(
            'contents_class_page' => array(
                            'type' => 'text',
                            'name' => t('Класс на странице, из которого будет браться содержание'),
                            'description' => t('Укажите название класса'),
                            'default' => 'page_content'
                        ),
            'contents_class_contents' => array(
                            'type' => 'text',
                            'name' => t('Класс в который будет записано содержимое'),
                            'description' => t('Укажите название класса'),
                            'default' => 'contents'
                        ),
            'contents_header' => array(
                            'type' => 'text',
                            'name' => t('Заголовок'),
                            'description' => t('Укажите заголовок'),
                            'default' => 'Содержание',
                        )
            ),
        t('Настройки плагина Содержание'),
        t('Укажите необходимые опции.')
    );
}



function contents_head($args = array()) 
{
	echo mso_load_jquery();
    $options = mso_get_option('plugin_contents', 'plugins', array());	
    if(!isset($options['contents_class_contents'])) $options['contents_class_contents'] = 'contents';
    if(!isset($options['contents_class_page'])) $options['contents_class_page'] = 'page_content';
    if(!isset($options['contents_header'])) $options['contents_header'] = t('Содержание');

	extract($options);

	echo <<<EOF
    <script>
    $(document).ready(function() {
        var cnt = $('.${contents_class_contents}');
        var contents_header = '${contents_header}';
        var old_lvl = 1;
        var lvl_diff = 0;
        var cnt_html = '';
        var header_html = '';
        var page = $('.${contents_class_page}');
        var arr = [1];
        header_html += '<p><strong>'+contents_header+'</strong></p>';
        page.find('h1,h2,h3,h4,h5').each(function(index)
        {
            $(this).prepend('<a name="part'+index+'"></a>');
            var text = $(this).text();
            var tag = this.tagName;
            var lvl = tag.substr(tag.length-1);
            var current_lvl = arr[arr.length-1]
            if(lvl>current_lvl)
            {
                cnt_html+= '<ul>';
                arr.push(lvl);
            }
            else if(lvl<current_lvl)
            {
                while(lvl<=arr[arr.length-2])
                {
                    arr.pop();
                    cnt_html+= '</ul>';
                }
                if(lvl<arr[arr.length-1]) arr[arr.length-1] = lvl;
            }
            cnt_html += ('<li><a href="#part'+index+'">'+text+'</a></li>');
        });
        cnt.html(header_html+'<ul>'+cnt_html+'</ul>');
    });
    </script>
EOF;

}

function contents_content($text = '')
{
//	$url = getinfo('plugins_url') . 'lightbox/images/';
	
    $options = mso_get_option('plugin_contents', 'plugins', array());	
    if(!isset($options['contents_class_contents'])) $options['contents_class_contents'] = 'contents';
    extract($options);

	$preg = array(
		'~\[contents\]~si' => '<div class="'.$contents_class_contents.'">$1</div>',
	);

	return preg_replace(array_keys($preg), array_values($preg), $text);
}



# функция, которая берет настройки из опций виджетов
function contents_widget($num = 1) 
{
    $widget = 'contents_widget_' . $num; // имя для опций = виджет + номер
    // заменим заголовок, чтобы был в  h2 class="box"
    
    $options = mso_get_option('plugin_contents', 'plugins', array());	


    if(!isset($options['contents_class_contents'])) $options['contents_class_contents'] = 'contents';

	extract($options);

    return contents_widget_custom($options, $num);
}

# функции плагина
function contents_widget_custom($options = array(), $num = 1)
{
    
    $options = mso_get_option('plugin_contents', 'plugins', array());	
    if(!isset($options['contents_class_contents'])) $options['contents_class_contents'] = 'contents';
    if(!isset($options['contents_header'])) $options['contents_header'] = t('Содержание');

	extract($options);
    // кэш 
    $cache_key = 'contents_widget_custom' . serialize($options) . $num;
    $k = mso_get_cache($cache_key);
    if ($k) return $k; // да есть в кэше
    
    $out = '';
    $out .= '<div class="'.$contents_class_content.'">$1</div>';
    
    mso_add_cache($cache_key, $out); // сразу в кэш добавим
    
    return $out;    
}


# end file
