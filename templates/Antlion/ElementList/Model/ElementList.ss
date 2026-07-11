	
	<% if $ShowTitle %>
	<h2 class="element-title">$Title</h2>
	<% end_if %>
	<% if $Content %>
		
			<div class="element-content">
				$Content
			</div>
		
	<% end_if %>

	<div class="list-element__grid element grid-x <% if not $NoGridSpace %>grid-margin-x grid-margin-y<% end_if %> small-up-2 medium-up-{$MediumBreakpointColumnCount} large-up-{$ColumnCount} {$VerticalAlignClass} {$HorizontalAlignClass}" data-listelement-count="$Elements.Elements.Count">
		<% loop $Elements.Elements %>
			<div class="list-element_block cell">{$Me}</div>
		<% end_loop %>
	</div>
	