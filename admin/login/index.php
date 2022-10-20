<?php
session_start();
session_destroy();

require_once("../../global/index.php");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Login votação</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="images/icons/favicon.ico" />
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<!--===============================================================================================-->
</head>

<body>

	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-form-title" style="background-image: url(images/bg-01.jpg);">
					<span class="login100-form-title-1">
						Login
					</span>
				</div>

				<form class="login100-form validate-form" method="POST" id="login_form">
					<div class="wrap-input100 validate-input m-b-26" data-validate="Instituição é obrigatória">
						<span class="label-input100">Instituíção</span>
						<input class="input100" id="instituicao" type="text" name="instituicao" value="unifebe" readonly placeholder="Insira a sua Instituição">
						<span class="focus-input100"></span>
					</div>

					<div class="wrap-input100 validate-input m-b-26" data-validate="Login é obrigatório">
						<span class="label-input100">Login</span>
						<input class="input100" type="text" name="login" placeholder="Insira um login">
						<span class="focus-input100"></span>
					</div>

					<div class="wrap-input100 validate-input m-b-18" data-validate="Senha é obrigatória">
						<span class="label-input100">Senha</span>
						<input class="input100" type="password" name="senha" placeholder="Insira uma senha">
						<span class="focus-input100"></span>
					</div>

					<div class="flex-sb-m w-full p-b-30">
						<!-- <div class="contact100-form-checkbox">
							<input class="input-checkbox100" id="ckb1" type="checkbox" name="remember-me">
							<label class="label-checkbox100" for="ckb1">
								Remember me
							</label>
						</div> -->

						<!-- <div>
							<a href="#" class="txt1" onclick="lembrar(this.value)">
								Forgot Password?
							</a>
						</div> -->
					</div>

					<div class="container-login100-form-btn">
						<button id="login_btn" class="login100-form-btn" type="submit">
							Login
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!--===============================================================================================-->
	<script src="vendor/jquery/jquery-3.2.1.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/animsition/js/animsition.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/bootstrap/js/popper.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/daterangepicker/moment.min.js"></script>
	<script src="vendor/daterangepicker/daterangepicker.js"></script>
	<!--===============================================================================================-->
	<script src="vendor/countdowntime/countdowntime.js"></script>
	<!--===============================================================================================-->
	<script src="js/main.js"></script>
	<!--===============================================================================================-->
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

<script>
	const baseURL = '<?php echo BASEURL ?>';

	$("#login_form").submit(function(e) {
		e.preventDefault();

		const dados = jQuery(this).serialize();

		$.ajax({
			method: 'POST',
			url: `${baseURL}/endpoint/sessao.php?method=logar`,
			dataType: 'json',
			data: dados,
			success: function(resposta) {
				if (resposta.erro == true) {
					swal.fire('', resposta.msg, 'error');
					return false;
				}

				window.location.replace(`./guardarSessao.php?instituicao=${$('#instituicao').val()}&nome=${resposta.usuario.nome}&email=${resposta.usuario.email}&permissao=${resposta.usuario.permissao}&periodo=${resposta.usuario.periodo}&token=${encodeURIComponent(resposta.token)}`);

			},
			error: function(erro) {
				console.log("Erro", erro);
			}

		})

	})
</script>

</html>