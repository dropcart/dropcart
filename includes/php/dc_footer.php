

</div><!-- /container -->

<div id="footer">
	<div class="container">
		<div class="col-md-12">
		<p>&copy; <?php echo date("Y"); ?> - <?php echo getContent('html_footer_copyright'); ?></p>
		</div><!-- /col -->

		<div class="col-md-12">
		<p class="pull-right">
			<?php
			$strSQL = "SELECT id, navTitle FROM ".DB_PREFIX."pages_content WHERE online = 1 ORDER BY navTitle ASC";
			$result = $objDB->sqlExecute($strSQL);

			while ($objPages = $objDB->getObject($result)) {
				echo '<a href="/dc_page.php?id='.$objPages->id.'">'.$objPages->navTitle.'</a> | ';
			}

			?>
			<a href="#">Omhoog</a>
		</p>
		</div><!-- /col -->
		<div class="col-xs-12 powered-by">
		<p class="text-center"><?php echo getContent('html_footer_powered'); ?> <a href="http://www.dropcart.nl/">DropCart <?php echo DROPCART_VERSION; ?></a></p>
		</div><!-- /col -->
	</div><!-- /container -->
</div><!-- /footer -->

<div class="modal fade" id="disclaimerDelivery" tabindex="-1" role="dialog" aria-labelledby="disclaimerDeliveryLabel" aria-hidden="true">
	<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-body">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<?php echo getContent('page_delivery_info'); ?>
		</div><!-- /modal body -->
		<div class="modal-footer">
			<a data-dismiss="modal" class="btn btn-primary">Oké, dat begrijp ik</a></button>
		</div><!-- /modal footer -->
	</div><!-- /modal content -->
	</div><!-- /modal dialog -->
</div><!-- /modal -->

</body>
</html>