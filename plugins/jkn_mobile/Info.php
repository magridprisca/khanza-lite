<?php
return [
    'name'          =>  'JKN Mobile',
    'description'   =>  'Modul Khanza JKN Mobile API',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '3.*',
    'icon'          =>  'tasks',
    'pages'         =>  ['JKN Mobile' => 'jknmobile'],
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_loket` (
          `kd` int(50) NOT NULL,
          `type` varchar(50) NOT NULL,
          `noantrian` varchar(50) NOT NULL,
          `postdate` date NOT NULL,
          `start_time` time NOT NULL,
          `end_time` time NOT NULL DEFAULT '00:00:00'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_antrian_loket`
            ADD PRIMARY KEY (`id`);');

        $core->db()->pdo()->exec('ALTER TABLE `lite_antrian_loket`
            MODIFY `id` int(50) NOT NULL AUTO_INCREMENT;');

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `lite_antrian_referensi` (
          `tanggal_periksa` date NOT NULL,
          `nomor_referensi` varchar(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec('ALTER TABLE `lite_antrian_referensi`
            ADD PRIMARY KEY (`nomor_referensi`);');

        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'username', '')");
        $core->db()->pdo()->exec("INSERT INTO `lite_options` (`module`, `field`, `value`) VALUES ('jkn_mobile', 'password', '')");
    },
    'uninstall'     =>  function () use ($core) {
        $core->db()->pdo()->exec("DELETE FROM `lite_options` WHERE `module` = 'jkn_mobile'");
    }
];