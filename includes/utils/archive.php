<?php

class archive{
	function archive($name){
		$this->options = array (
			'basedir' => ".",
			'name' => $name,
			'prepend' => '',
			'overwrite' => 0,
			'recurse' => 1,
			'storepaths' => 1,
			'followlinks' => 0,
			'level' => 3,
			'method' => 1,
			'type' => '',
		);
		$this->files = array();
		$this->exclude = array();
		$this->error = array();
		$this->warning = array();
	}
	
	function clear(){
		$this->files = array();
		$this->exclude = array();
		$this->error = array();
		$this->warning = array();
	}

	function set_options($options){
		foreach ($options as $key => $value)
			$this->options[$key] = $value;
		if (!empty ($this->options['basedir']))
		{
			$this->options['basedir'] = str_replace("\\", "/", $this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/+/", "/", $this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/$/", '', $this->options['basedir']);
		}
		if (!empty ($this->options['name']))
		{
			$this->options['name'] = str_replace("\\", "/", $this->options['name']);
			$this->options['name'] = preg_replace("/\/+/", "/", $this->options['name']);
		}
		if (!empty ($this->options['prepend']))
		{
			$this->options['prepend'] = str_replace("\\", "/", $this->options['prepend']);
			$this->options['prepend'] = preg_replace("/^(\.*\/+)+/", '', $this->options['prepend']);
			$this->options['prepend'] = preg_replace("/\/+/", "/", $this->options['prepend']);
			$this->options['prepend'] = preg_replace("/\/$/", '', $this->options['prepend']) . "/";
		}
	}

	function create_archive(){
		$pwd = getcwd();
		chdir($this->options['basedir']);
		if ($this->options['overwrite'] == 0 && file_exists($this->options['name'] . ($this->options['type'] == "gzip" ? ".tmp" : ''))){
			$this->error[] = "File {$this->options['name']} already exists.";
			chdir($pwd);
			return 0;
		}
		else if ($this->archive = @fopen($this->options['name'] . ($this->options['type'] == "gzip" ? ".tmp" : ''), "wb+"))
			chdir($pwd);
		else
		{
			$this->error[] = "Could not open {$this->options['name']} for writing.";
			chdir($pwd);
			return 0;
		}

		switch ($this->options['type']){
		case "gzip":
			if (!$this->create_tar()){
				$this->error[] = "Could not create tar file.";
				return 0;
			}
			if (!$this->create_gzip()){
				$this->error[] = "Could not create gzip file.";
				return 0;
			}
			break;
		case "tar":
			if (!$this->create_tar()){
				$this->error[] = "Could not create tar file.";
				return 0;
			}
		}

		fclose($this->archive);
		if ($this->options['type'] == "gzip") unlink($this->options['name'].'.tmp');
	}

	function add_files($list)
	{
		$temp = $this->list_files($list);
		foreach ($temp as $current) $this->files[] = $current;
	}

	function exclude_files($list){
		$this->exclude = $list; 
	}

	function list_files($list){
		if (!is_array ($list)) {
			$temp = $list;
			$list = array($temp);
			unset ($temp);
		}

		$files = array();

		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach ($list as $current){
			//if ($current == "." || $current == "..") continue;
			$current = str_replace("\\", "/", $current);
			$current = preg_replace("/\/+/", "/", $current);
			$current = preg_replace("/\/$/", '', $current);
			if (strstr($current, "*")){
				$regex = preg_replace("/([\\\^\$\.\[\]\|\(\)\?\+\{\}\/])/", "\\\\\\1", $current);
				$regex = str_replace("*", ".*", $regex);
				$dir = strstr($current, "/") ? substr($current, 0, strrpos($current, "/")) : ".";
				$temp = $this->parse_dir($dir);
				foreach ($temp as $current2)
					if (preg_match("/^{$regex}$/i", $current2['name'])) $files[] = $current2;
				unset ($regex, $dir, $temp, $current);
			} else if (@is_dir($current)) {
				if (strMatchMask($current, $this->exclude)) {
					$this->warning[] = "Excluded: <b>{$current}</b>";
				} else {
					$temp = $this->parse_dir($current);
					foreach ($temp as $file) {
						if (strMatchMask($file['name'], $this->exclude)) {
							$this->warning[] = "Excluded: <b>{$file['name']}</b>";
							continue;
						}
						$files[] = $file;
					}
				}
				unset ($temp, $file);
			} else if (@file_exists($current) && !is_dir($current))
				$files[] = array ('name' => $current, 'name2' => $this->options['prepend'] .
					preg_replace("/(\.+\/+)+/", '', ($this->options['storepaths'] == 0 && strstr($current, "/")) ?
					substr($current, strrpos($current, "/") + 1) : $current),
					'type' => @is_link($current) && $this->options['followlinks'] == 0 ? 2 : 0,
					'ext' => substr($current, strrpos($current, ".")), 'stat' => stat($current));
		}

		chdir($pwd);
		unset ($current, $pwd);
		//usort($files, array ("archive", "sort_files"));
		return $files;
	}

	function parse_dir($dirname)
	{
		/*if ($this->options['storepaths'] == 1 && !preg_match("/^(\.+\/*)+$/", $dirname))
			$files = array (array ('name' => $dirname, 'name2' => $this->options['prepend'] .
				preg_replace("/(\.+\/+)+/", '', ($this->options['storepaths'] == 0 && strstr($dirname, "/")) ?
				substr($dirname, strrpos($dirname, "/") + 1) : $dirname), 'type' => 5, 'stat' => stat($dirname)));
		else*/ $files = array();
		$dir = @opendir($dirname);
			
		while ($file = @readdir($dir)){
			$fullname = $dirname . "/" . $file;
			if ($file == "." || $file == "..") continue;
			elseif (strMatchMask($fullname, $this->exclude)) {
				$this->warning[] = "Excluded: <b>{$fullname}</b>";
				continue;
			} elseif (@is_dir($fullname)){
				if (empty($this->options['recurse'])) continue;
				$temp = $this->parse_dir($fullname);
				foreach ($temp as $file2) $files[] = $file2;
			}
			else if (@file_exists($fullname))
				$files[] = array ('name' => $fullname, 'name2' => $this->options['prepend'] .
					preg_replace("/(\.+\/+)+/", '', ($this->options['storepaths'] == 0 && strstr($fullname, "/")) ?
					substr($fullname, strrpos($fullname, "/") + 1) : $fullname),
					'type' => @is_link($fullname) && $this->options['followlinks'] == 0 ? 2 : 0,
					'ext' => substr($file, strrpos($file, ".")), 'stat' => stat($fullname));
		}
		@closedir($dir);
		return $files;
	}
}

class tar_file extends archive{
	function tar_file($name){
		$this->archive($name);
		$this->options['type'] = "tar";
	}

	function create_tar(){
		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach ($this->files as $current){
			if ($current['name'] == $this->options['name']) continue;
			if (strlen($current['name2']) > 99){
				$path = substr($current['name2'], 0, strpos($current['name2'], "/", strlen($current['name2']) - 100) + 1);
				$current['name2'] = substr($current['name2'], strlen($path));
				if (strlen($path) > 154 || strlen($current['name2']) > 99){
					$this->error[] = "Could not add {$path}{$current['name2']} to archive because the filename is too long.";
					continue;
				}
			}
			$block = pack("a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", $current['name2'], sprintf("%07o", 
				$current['stat'][2]), sprintf("%07o", $current['stat'][4]), sprintf("%07o", $current['stat'][5]), 
				sprintf("%011o", $current['type'] == 2 ? 0 : $current['stat'][7]), sprintf("%011o", $current['stat'][9]), 
				"        ", $current['type'], $current['type'] == 2 ? @readlink($current['name']) : '', "ustar ", " ", 
				"Unknown", "Unknown", '', '', !empty ($path) ? $path : '', '');

			$checksum = 0;
			for ($i = 0; $i < 512; $i++) $checksum += ord(substr($block, $i, 1));
			$checksum = pack("a8", sprintf("%07o", $checksum));
			$block = substr_replace($block, $checksum, 148, 8);

			if ($current['type'] == 2 || $current['stat'][7] == 0) {
				if (fwrite($this->archive, $block) === false){
					$this->error[] = "tar_file: cant write to file, check disk space";
					break;
				}
			} elseif ($fp = @fopen($current['name'], "rb")) {
				if (fwrite($this->archive, $block) === false){
					$this->error[] = "tar_file: cant write to file, check disk space";
					break;
				}

				while ($temp = fread($fp, N_KBYTE*100))
				if (fwrite($this->archive, $temp) === false){
					$this->error[] = "tar_file: cant write to file, check disk space";
					break 2;
					
				}

				if ($current['stat'][7] % 512 > 0){
					$temp = '';
					for ($i = 0; $i < 512 - $current['stat'][7] % 512; $i++) $temp .= "\0";
					if (fwrite($this->archive, $temp) === false){
						$this->error[] = "tar_file: cant write to file, check disk space";
						break;
					}
				}
				fclose($fp);
			} else $this->error[] = "Could not open file {$current['name']} for reading. It was not added.";
		}

		fwrite($this->archive, pack("a1024", ''));
		chdir($pwd);
		return 1;
	}

	function extract_files($checkOnly = false)
	{
		$pwd = getcwd();
		chdir($this->options['basedir']);

		if ($fp = $this->open_archive()){
			while ($block = fread($fp, 512)){
				$temp = unpack("a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2temp/a32temp/a32temp/a8temp/a8temp/a155prefix/a12temp", $block);
				$file = array (
					'name' => $temp['prefix'] . $temp['name'],
					'stat' => array (
						2 => $temp['mode'],
						4 => octdec($temp['uid']),
						5 => octdec($temp['gid']),
						7 => octdec($temp['size']),
						9 => octdec($temp['mtime']),
					),
					'checksum' => octdec($temp['checksum']),
					'type' => $temp['type'],
					'magic' => $temp['magic'],
				);
				if ($file['checksum'] == 0x00000000)
					break;
				else if (substr($file['magic'], 0, 5) != "ustar")
				{
					$this->error[] = "This script does not support extracting this type of tar file.";
					break;
				}
				$n = (512 - $file['stat'][7] % 512) == 512 ? 0 : (512 - $file['stat'][7] % 512);
				
				$block = substr_replace($block, "        ", 148, 8);
				$checksum = 0;
				for ($i = 0; $i < 512; $i++) $checksum += ord(substr($block, $i, 1));
				if ($file['checksum'] != $checksum) $this->error[] = "Could not extract from {$this->options['name']}, it is corrupt.";

				$writeError = false;
				if ($this->options['overwrite'] == 0 && file_exists($file['name'])){
					$this->error[] = "{$file['name']} already exists.";
					continue;
				}elseif ($checkOnly){
					$s = ($file['stat'][7] > 0) ? fread($fp, $file['stat'][7]) : '';
					if ($n > 0) fread($fp, $n);
				} else {
					$d = dirname($file['name']);
					if ($d && !is_dir($this->options['basedir'].'/'.$d)) mkdir($this->options['basedir'].'/'.$d, NULL, true);
					if ($new = @fopen($file['name'], "wb")){
						$s = ($file['stat'][7] > 0) ? fread($fp, $file['stat'][7]) : '';
						fwrite($new, $s);
						if ($n > 0) fread($fp, $n);
						fclose($new);
					} else $writeError = true;
					//chmod($file['name'], $file['stat'][2]);
				}
				
				if ($writeError){
					$this->error[] = "Could not open {$file['name']} for writing.";
					continue;
				}
				if (!$checkOnly){
					@touch($file['name'], $file['stat'][9]);
					//chown($file['name'], $file['stat'][4]);
					//chgrp($file['name'], $file['stat'][5]);
				}
				unset ($file);
			}
		} else $this->error[] = "Could not open file {$this->options['name']}";
		chdir($pwd);
	}

	function open_archive()
	{
		return @fopen($this->options['name'], "rb");
	}
}

class gzip_file extends tar_file{
	function gzip_file($name){
		$this->tar_file($name);
		$this->options['type'] = "gzip";
	}

	function create_gzip(){
		$pwd = getcwd();
		chdir($this->options['basedir']);
		if ($fp = gzopen($this->options['name'], "wb{$this->options['level']}")){
			fseek($this->archive, 0);
			while ($temp = fread($this->archive, N_MBYTE))
				gzwrite($fp, $temp);
			gzclose($fp);
			chdir($pwd);
		} else {
			$this->error[] = "Could not open {$this->options['name']} for writing.";
			chdir($pwd);
			return 0;
		}
		return 1;
	}

	function open_archive(){
		return gzopen($this->options['name'], "rb");
	}
}

?>