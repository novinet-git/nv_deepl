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

		$options = "";
		foreach(rex_clang::getAll() as $lang) {
			if($lang->getId() == $cid) continue;
			$options .= '<option value="' . $lang->getId() . '">' . $lang->getName() . '</option>';
		} 

		$script = <<<JS
			var select = document.getElementById("nv_deepl-lang-translate-select");
			var link = document.getElementById("nv_deepl-lang-translate-link");

			function nv_deepl_change_link(_) {
				// get the current selected lang id
				var value = select.value;
				var href = link.getAttribute("href");
				// deconstruct the href of the link
				var split = href.split("&");
				var buffer = [];
				for(var i = 0; i < split.length; i++) {
					var ele = split[i];
					var inner_split = ele.split("=");
					
					if(inner_split[0] && inner_split[0] == "target_clang_id") {
						// set the current selected lang id
						inner_split[1] = value;
					}

					buffer.push(inner_split.join("="));
				}

				// rebuild the href and set it
				link.setAttribute("href", buffer.join("&"));
			}

			if(select && link) {
				nv_deepl_change_link();	
				select.addEventListener("change", nv_deepl_change_link.bind(this));
			}

			if(link) {
				link.addEventListener("click", function(event) {
					event.preventDefault();
					var width = window.innerWidth * 0.66 ;
					// define the height in
					var height = width * window.innerHeight / window.innerWidth ;
					// Ratio the hight to the width as the user screen ratio
					window.open(this.href , 'newwindow', 'width=' + width + ', height=' + height + ', top=' + ((window.innerHeight - height) / 2) + ', left=' + ((window.innerWidth - width) / 2));
				});
			}
		
		JS;

		$panel = "";
		$panel .= <<<HTML
			<div class="nv_deepl">
				<div class="form-group">
					<label class="control-label" for="nv_deepl-lang-translate-select">Übersetzen in</label>
					<select class="form-control" id="nv_deepl-lang-translate-select">
						$options
					</select>
				</div>
				<a id="nv_deepl-lang-translate-link" href="/redaxo/index.php?page=nv_deepl/translate&article_id=$aid&clang_id=$cid&target_clang_id=2" class="btn btn-primary pull-left" target="_blank">Übersetzen</a>
				<script type="text/javascript">$script</script>
			</div>	
		HTML;


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


	static function translate($sText,$sLangCode,$sSourceCode="DE") {
		$oAddon = rex_addon::get(self::$oAddon);

		$sApiKey = $oAddon->getConfig("api_key");

		$aParams = [
			'auth_key' => $sApiKey,
			'text' => $sText,
			'target_lang'   => $sLangCode,
			'source_lang' => $sSourceCode
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

	static function get_usage() {
		$oAddon = rex_addon::get(self::$oAddon);

		$sApiKey = $oAddon->getConfig("api_key");

		$aParams = [
			'auth_key' => $sApiKey,
		];

		$ch = curl_init('https://api-free.deepl.com/v2/usage');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $aParams);

		// execute!
		$oResponse = curl_exec($ch);
	
		// close the connection, release resources used
		curl_close($ch);

		return $oResponse;
	}
}
