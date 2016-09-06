<?php

return array(
	//'配置项'=>'配置值'

	//ERROR
	'SHOW_ERROR_MSG'	=>	true,
	'SHOW_PAGE_TRACE'	=>	true,

	//URI
	'MULTI_MODULE'	=>	true,
	'MODULE_ALLOW_LIST'	=>	array('Home', 'Api', 'Admin', 'Portal'),
	'MODULE_DENY_LIST'	=>	array('Runtime'),
	'URL_MODE'			=>	2,
	'URL_PATHINFO_DEPR'	=>	'/',
	'URL_HTML_SUFFIX'	=>	'',

	'TMPL_PARSE_STRING'	=> array(
		'__PUBLIC__'	=>	'/static',
	),
	'DEFAULT_FILTER'	=>	'', //不启用过滤
	'DEFAULT_MODULE'	=>	'Admin',



	//Libvirt Config
	'LIBVIRT'			=>	array(
		'host'			=>	'192.168.0.60',		//libvirt host
		'type'			=>	'TCP',				//Libvirt connect type
		'upload_file_type'	=>	array('raw','iso','qcow2','qcow'),
		'max_guests'		=>	'50',		//单个host最大guests数量
		'storage_path'		=>	'/home/data/images/',	//虚拟机存储位置
		'iso_storage_path'	=>	'/home/data/iso/',		//ISO镜像存储位置
		'increment_storage_path'=>	'/home/data/images/incr/',	//增量镜像存储位置
		'backup_storage_path'	=>	'/home/data/images/backup/',	//备份存储位置
		),

	//vm system config
	'SYSTEM'			=> array(
		'disk_driver'	=>	'qcow2',			//磁盘驱动
		'disk_bus'		=>	'ide',				//IDE接口 | SCSI接口
		'disk_dev'		=>	'sdb',				//SATA接口 硬盘
		'nic_type'		=>	'bridge',			//桥接
		'nic_inet'		=>	'virbr0',			//虚拟网卡 [rtl8139|default|virtio]
		'features'		=>	'acpi|apic|pae',	//系统特性(ACPI[电源管理]|PAE[物理地址扩展]|APIC[驱动中断])
		'cpucount'		=>	'1',				//CPU数量
		'os'			=>	'x86_64',			//全虚拟化
		'memory'		=>	'512000',		//默认Kib
		'maxmem'		=>	'1024000',		//最大内存
		'clock'			=>	'localtime',		//时钟
		'persistent'		=>	'1',				//持久化
		'disk_default_size'	=>	'10',				//默认5G磁盘大小
		),
	'VNC'				=> array(
		'host'			=>	'http://192.168.0.60',		//VNC PROXY HOST
		'port'			=>	'6080',				//VNC PROXY PORT
		'uri'			=>	'/view/?token=',	//VNC URI
		'config_path'	=>	'/home/data/novnc/',		//端口映射文件
		'config_name'	=>	'vncmap.conf',		//映射文件名
		),
);
