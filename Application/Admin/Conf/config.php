<?php
return array(
	//'配置项'=>'配置值'
	'URL_MODEL' => 1,
	'VIEW_PATH' => './Tpl/',
	'DEFAULT_THEME' => 'Admin',
	'TMPL_PARSE_STRING' => array(
			'__JS__'     => '/Tpl/Admin/js',
			'__CSS__'     => '/Tpl/Admin/css',
			'__IMG__'     => '/Tpl/Admin/images',
	),
	// 'SHOW_PAGE_TRACE' => true,
	'dividedTime'		=> 1,			//设置定在距发货后多长时间才可以分成，初始值是 7天（604800）

    
    /**
     * @author peng	
     * @date 2017-01-03
     * @descreption 
     */
     'rank_name_arr'  =>[
                        1=>'高级',
                        2=>'中级',
                        3=>'低级',
                    ]
);