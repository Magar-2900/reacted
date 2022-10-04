<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reset Password</title>
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js" integrity="sha512-37T7leoNS06R80c8Ulq7cdCDU5MNQBwlYoy1TX/WUsLFC2eYNqtKlV0QjH7r8JpG/S0GUMZwebnVFLPd6SU5yg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.min.js" integrity="sha512-XZEy8UQ9rngkxQVugAdOuBRDmJ5N4vCuNXCh8KlniZgDKTvf7zl75QBtaVG1lEhMFe2a2DuA22nZYY+qsI2/xA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
	<style type="text/css">
		.field-icon {
		  float: right;
		  margin-left: -25px;
		  margin-right: 7px;
		  margin-top: -25px;
		  position: relative;
		  z-index: 2;
		}

		.container{
		  padding-top:50px;
		  margin: auto;
		}
		.error{
			color: #de0202;
		}
		.container{
			 margin: auto;
			  width: 50%;
			  border: 1px solid black;
			  padding: 10px;

		}
		h1{
			text-align: center;
		}
	</style>
	
</head>
<body>
		
	<div class="container">
		<h1>Reset Password</h1>
			<form method="post" id="reset-password" >
			    <label>Security Code</label>
			    <div class="form-group pass_show"> 
	                <input type="text" class="form-control" placeholder="Security Code" name="security_code" id="security_code" required> 
	            </div> 
			       <label>New Password</label>
	            <div class="form-group pass_show"> 
	                <input type="password"  class="form-control" placeholder="New Password" name="new_password" id="new_password" required>
	            </div> 
			       <label>Confirm Password</label>
	            <div class="form-group pass_show"> 
	                <input type="password"  class="form-control" placeholder="Confirm Password" name="confirm_password" id="password-field" required> 
	                <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
	            </div> 
			    <button type="submit" id="loginbtn" class="btn btn-primary">Login</button>
		    </form>
		</div>  
	
	
</body>
</html>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js"></script>
<script type="text/javascript">

	$(".toggle-password").click(function() {
	  $(this).toggleClass("fa-eye fa-eye-slash");
	  var input = $($(this).attr("toggle"));
	  if (input.attr("type") == "password") {
	    input.attr("type", "text");
	  } else {
	    input.attr("type", "password");
	  }
	});

	function toastify(text, classname, duration){
	    Toastify({
	        text: text,
	        className: classname,
	        duration: duration,
	        close: true
	    }).showToast();
	}

	$("#reset-password").submit(function(e) {
    	e.preventDefault();
	}).validate({
		rules :{
			'security_code':{
				required:true
			},
			'new_password':{
				required:true
			},
			'confirm_password':{
				required:true,
				equalTo : "#new_password"
			},
		},
		messages:{
			'security_code':{
				required:'Please Enter security code.'
			},
			'new_password':{
				required:'Please Enter new password.'
			},
			'confirm_password':{
				required:'Please Enter confirm password.',
				equalTo : "Passwords do not match."
			}
		},
		submitHandler : function(form){
			
			var security_code = $("input[name=security_code]").val();
			var new_password = $("input[name=new_password]").val();
			var confirm_password = $("input[name=confirm_password]").val();
			var site_url = "<?php echo $this->config->item('base_url')?>";
			var urlParams = new URLSearchParams(window.location.search)
			var email = urlParams.get('rsp');
			
			$.ajax({
				type:'POST',
				url: site_url+'Authentication/reset_password_action',
				data:{'security_code':security_code,'new_password':new_password,'confirm_password':confirm_password,'email':email},
				success:function (res) {
					var data = JSON.parse(res);
					console.log(data);
					if(data.status == 1)
					{
						toastify(data.meassage, 'success', 10000);
					}
					else
					{
						toastify(data.meassage, 'danger', 10000);
					}
				}
			})
			return false;
		}
	})
</script>