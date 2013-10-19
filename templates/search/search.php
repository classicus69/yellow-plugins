<?php $pages = getSearchPages($yellow, 5, $_REQUEST["query"]) ?>
<?php $yellow->snippet("header") ?>
<?php $yellow->snippet("navigation") ?>
<div class="content search">
<h1><?php echo $yellow->page->getHtml("title") ?></h1>
<form action="<?php echo $yellow->pages->serverBase ?>/search/" method="post" class="search-form">
<input type="text" name="query" value="<?php echo htmlspecialchars($_REQUEST["query"]) ?>" class="search-text" />
<input type="submit" value="<?php echo $yellow->text->getHtml("searchButton") ?>" class="search-btn" />
<input type="hidden" name="clean-url" />
</form>
<?php foreach($pages as $page): ?>
<div class="result">
<div class="entry-header"><h2><a href="<?php echo $page->getLocation() ?>"><?php echo $page->getHtml("title") ?></a></h2></div>
<div class="entry-content"><?php echo htmlspecialchars($yellow->toolbox->createTextDescription($page->getContent(), 250)) ?></div>
<div class="entry-location"><a href="<?php echo $page->getLocation() ?>"><?php echo $yellow->config->get("serverName").$page->getLocation() ?></a></div>
</div>
<?php endforeach ?>
<?php if(!count($pages)): ?>
<p><?php echo $yellow->text->get(isset($_REQUEST["query"]) ? "searchResultsNone" : "searchQueryNone") ?></p>
<?php endif ?>
<?php $yellow->snippet("pagination", $pages) ?>
</div>
<?php $yellow->snippet("footer") ?>
<?php if(PHP_SAPI=="cli" && !isset($_REQUEST["query"])) $yellow->page->error(500, "Server-side scripting needed!") ?>
<?php $yellow->header("Last-Modified: ".$pages->getModified(true)) ?>
<?php function getSearchPages($yellow, $limit, $query)
{
	$pages = $yellow->pages->create();
	$tokens = array_slice(array_unique(array_filter(explode(' ', $query), "strlen")), 0, 10);
	if(!empty($tokens))
	{
		$query = trim($query);
		$yellow->page->set("titleHeader", $query." - ".$yellow->page->get("sitename"));
		$yellow->page->set("title", $yellow->text->get("searchQuery")." ".$query);
		foreach($yellow->pages->index() as $page)
		{
			$searchScore = 0;
			$searchTokens = array();
			foreach($tokens as $token)
			{
				$score = substr_count(strtoloweru($page->getContent(true)), strtoloweru($token));
				if($score) { $searchScore += $score; $searchTokens[$token] = true; }
				if(stristr($page->getLocation(), $token)) { $searchScore += 20; $searchTokens[$token] = true; }
				if(stristr($page->get("title"), $token)) { $searchScore += 20; $searchTokens[$token] = true; }
				if(stristr($page->get("author"), $token)) { $searchScore += 5; $searchTokens[$token] = true; }
				if(stristr($page->get("tag"), $token)) { $searchScore += 5; $searchTokens[$token] = true; }
			}
			if(count($tokens) == count($searchTokens))
			{
				$page->set("searchscore", $searchScore);
				$pages->append($page);
			}
		}
		$pages->sort("searchscore")->pagination($limit);
	}
	return $pages;
}
?>