<?php class nvDeepl
{
	static $oAddon = "nv_deepl";

	static function getPanel($ep)
	{
		$oAddon = rex_addon::get(self::$oAddon);

		//Vorgaben einlesen/setzen
		$op = $ep->getSubject();												//Content des ExtPoint (z.B. Seiteninhalt)
		$params = $ep->getParams();											//alle Parameter des ExtPoint holen (z.B. Article-ID)
		$aid = $params['article_id'];											//ID des Artikels
		$cid = $params['clang'];												//ID der Sprachversion
		$ctype = $params['ctype'];												//ID der Spalte


		$panel = "";
		$panel .= <<<EOD
	<div class="nv_deepl">
		<form>
			<div class="rex-js-widget">
				<div class="input-group">
					<input class="form-control" type="text" name="seocu-keyword" value="$keyword" placeholder="$l1" data-seocu-aid="$aid" data-seocu-cid="$cid" />
					<span class="input-group-btn">
						<a class="btn btn-popup" title="$l2"><i class="rex-icon fa-refresh"></i></a>
					</span>
				</div>
			</div>
		</form>
		<br>
		<a href="/redaxo/index.php?page=nv_deepl/translate&article_id=$aid&ctype=$ctype&clang_id=$cid&target_clang_id=2" class="btn btn-primary pull-left" target="_blank">Übersetzen</a>
	</div>	
EOD;


		//SEO-Panel erstellen und ausgeben
		$collapsed = false;
		$frag = new rex_fragment();
		$frag->setVar('title', '<div class="seocu-title"><i class="rex-icon fa-stethoscope"></i> nvDeepl<div class="seocu-resultbar-wrapper"><div class="seocu-resultbar"></div></div></div>', false);
		$frag->setVar('body', $panel, false);
		$frag->setVar('article_id', $aid, false);
		$frag->setVar('clang', $cid, false);
		$frag->setVar('ctype', $ctype, false);
		$frag->setVar('collapse', true);								//schließbares Panel - true|false
		$frag->setVar('collapsed', $collapsed);							//Panel geschlossen starten - true|false
		$cnt = $frag->parse('core/page/section.php');

		return $op . $cnt;
	}


	static function translate($sText,$sLangCode) {
		$oAddon = rex_addon::get(self::$oAddon);

		$sApiKey = $oAddon->getConfig("api_key");

		$aParams = [
			'auth_key' => $sApiKey,
			'text' => $sText,
			'target_lang'   => $sLangCode,
			'source_lang' => 'DE'
		];
		
		$ch = curl_init('https://api-free.deepl.com/v2/translate');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $aParams);
		
		// execute!
		$oResponse = curl_exec($ch);
		
		// close the connection, release resources used
		curl_close($ch);
		

		return $oResponse;

	}


}
