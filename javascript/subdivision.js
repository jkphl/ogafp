$(window).ready(function(){
	
	// Arm all region tables
	$('form.subdivision-type').each(function(){
		this.checkboxes			= $('input[type=checkbox]', this),
		selectAll				= $('input[type=button][name=select-all]', this),
		unselectAll				= $('input[type=button][name=unselect-all]', this);
		this.checkboxes.each(function(){ $(this).closest('tr').click($.proxy(function(){ this.checked = !this.checked; }, this )); });
		selectAll.click(function(){ this.form.checkboxes.each(function(){ this.checked = true; })});
		unselectAll.click(function(){ this.form.checkboxes.each(function(){ this.checked = false; })});
	});
	
	// Arm all language and romanization dropdowns
	$('select.romanization, select.language, select.script').change(function(e) {
		this.form.elements['priority'].value = $(this).attr('data-priority');
		this.form.submit();
	});
})