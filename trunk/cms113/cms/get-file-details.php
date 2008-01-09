<?
	/* 
		All files:
		 - Return match status
		 
		If file is a page:
		 - Return list of local child includes, with status code for each LCI
		 
		 Note: LIVE is the master collection...
	*/
	
	header("Content-type: text/xml") ;
	require "../cmsfns.php" ;
	
	// DEBUG	echo $_GET['file'] ; exit ;
	
	if (!isset($_GET['file'])) {
		echo 'error:No file name provided.' ;
		exit ;
	}
	
	/* 
		Get details of file itself (Page/FI)
	*/
	$matchStatus = false ;
	$liveFile = pathFromID($_GET['file']) ;
	
	/* 
		If $liveFile starts with "pagetemplates/"... it's a PT
		In which case, get the LCIs from pagetemplates/ptname/
	*/
	// echo 'error:' . $isPT ; exit ;
	
	if (isPageTemplate($liveFile)) {
		$xmlr = '<xml>' ;
		$xmlr .= '<file>' . $_GET['file'] . '</file>' ;
		$xmlr .= '<filestatus>0</filestatus>' ;
		getPTChildIncludes($liveFile) ;
	}
	else {
		$previewFile = getPreviewFileFromLive($liveFile) ;
		$matchStatus = matchFiles($previewFile,$liveFile) ;
		$xmlr = '<xml>' ;
		$xmlr .= '<file>' . $_GET['file'] . '</file>' ;
		$xmlr .= '<filestatus>' . $matchStatus . '</filestatus>' ;
		/* 
			If it's a page, we need details on all LCIs
		*/
		if (getFileExtension($liveFile) == "php") {
			$xmlr .= '<localchildincludes>' ;
			getChildIncludes($liveFile) ;
			$xmlr .= '</localchildincludes>' ;
		}
	}
	
	
	$xmlr .= "</xml>" ;
	print $xmlr ;
	exit ;
	
	
	
	/* 
		Functions
	*/
	function getChildIncludes($liveFile) {
		global $xmlr ;
		$localRootDir = getLCIRootFolderFromPagePath($liveFile) ;
		$localPreviewDir = $localRootDir . '/cms_preview' ;
		$localLiveDir = $localRootDir . '/cms_live' ;
		if (!is_dir($localPreviewDir) || !is_dir($localLiveDir)) {
			return false ;
		}
		$localPreviewDirHandle = opendir($localPreviewDir) ;
		while (False !== ($lciFile = readdir($localPreviewDirHandle))) {
			if ($lciFile != '.' & $lciFile != '..' && !is_dir($localPreviewDir . '/' . $lciFile)) {
				$xmlr .= '<lci>' ;
				$xmlr .= '<type>' . getFileExtension($lciFile) . '</type>' ;
				$xmlr .= '<filename>' . $lciFile . '</filename>' ;
				$xmlr .= '<filestatus>' . matchFiles($localPreviewDir . '/' . $lciFile, getLiveLCIFromPreview($localPreviewDir . '/' . $lciFile)) . '</filestatus>' ;
				$xmlr .= '</lci>' ;
			}
		}
		closedir($localPreviewDirHandle) ;
	}

	function getPTChildIncludes($liveFile) {
		// e.g. $liveFile = "pagetemplates/ptname/"
		global $xmlr ;
		$lciDir = PTSDIR . '/' .stripFileExtension(substr($liveFile, strlen(PTSDIR)+1)) ;
		if (!is_dir($lciDir)) {
			return false ;
			// Should we attempt to create folder??
		}
		$lciDirHandle = opendir($lciDir) ;
		while (False !== ($lciFile = readdir($lciDirHandle))) {
			if ($lciFile != '.' & $lciFile != '..' && !is_dir($lciDir . '/' . $lciFile)) {
				$xmlr .= '<lci>' ;
				$xmlr .= '<type>' . getFileExtension($lciFile) . '</type>' ;
				$xmlr .= '<filename>' . $lciFile . '</filename>' ;
				$xmlr .= '<filestatus>0</filestatus>' ;
				$xmlr .= '</lci>' ;
			}
		}
		closedir($lciDirHandle) ;
	}
?>
