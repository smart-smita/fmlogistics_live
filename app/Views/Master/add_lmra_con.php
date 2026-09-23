<div class="row">

	<div class="col-sm-12">
		<h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
	</div>
</div>

<div class="row mb-5">
<div class="col-md-12">
<div class="card">
<div class="card-body">
<div class="row">
	<div class="col-sm-12">
	
		<div class="card-box">
			<h4 class="header-title"><?php echo isset($title)?$title:"FORM"?></h4>
			<form class="POST_AJAX" action="<?php echo isset($action_url)?$action_url:""?>"
				method="POST">
				<div class="row">
					
                    <div class="col-md-12">
						<br>
                        <div class="form-group">
							<label for="lmra_category_id">LMRA Select Category</label> 
                            <select
								class="form-control select2" 
								name="lmra_category_id" >
								<option selected="selected" disabled="disabled">Select Category</option>
									<?php if(isset($lmra_category)) foreach($lmra_category as $row){ ?>
									<option
									value="<?=$row['category_id']?>"
									<?=(isset($lmra_category_id) && $lmra_category_id==$row['category_id'])?'selected="selected"':""?> ><?=$row['category_name']?></option>
									<?php } ?>
								</select>
						</div>
					</div>
					<!-- end col -->

				<div class="col-md-12">
                
						<br>
                        <div class="form-group">
							<label for="lmra_text">LMRA Question</label> <input type="text"
								class="form-control" id="lmra_text" placeholder="ASK LMRA QUESTION"
								name="lmra_text" required="required"  
								value="<?php echo isset($lmra_text)?$lmra_text:""?>">
						</div>
						
				
                </div>
                
				<div class="col-md-12">
                
						<br>
                        <div class="form-check">
                                                <?php if(isset($lmra_options)){ 
                                                    $lmra_options=explode(",",$lmra_options);
                                                } 
                                                //print_r($lmra_options);
                                                ?>		
                            <?php foreach($lmra_sevirity_options as $row){ ?>
                            
                            <label class="form-check-label">
                            <input type="checkbox"
								class="form-check-input" id="<?=$row['name']?>" 
								name="lmra_options[]" 
								value="<?=$row['name']?>" 
                                <?=(isset($lmra_options) && in_array($row['name'],$lmra_options,true))?"checked":""?>
                                ><?=$row['name']?></label> 
                            <?php } ?>
                            </div>
						
				
                </div>
                	
                <input type="text"
								class="form-control" id="lmra_category_text" placeholder="LMRA Category"
								name="lmra_category_text" required="required" style="display:none"
								value="<?php echo isset($lmra_category_text)?$lmra_category_text:""?>">

					<div class="col-md-12">
					<button ajax_events="true" value="save_html_data" data-url="<?php echo isset($action_url)?$action_url:""?>" type="submit" class="btn btn-purple waves-effect waves-light" style="margin-top: 15px; float: right;" onclick="event.preventDefault(); ajaxLoader(this,$(this).closest('form'));">Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	</div>
	</div>
</div>
</div>
</div>
<script>
function validatation(){
return true;
}
$('select[name=lmra_category_id]:input').on("change", function (e) {
	//children("option[value='"+$(this).val()+"']").text()
	$('input[name=lmra_category_text]:input').val(""+$(this).children("option[value='"+$(this).val()+"']").text());
});
</script>