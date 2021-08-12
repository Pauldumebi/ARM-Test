import { Link, useHistory } from 'react-router-dom';
import classes from './Login.module.css';
import { useForm } from 'react-hook-form';
import axios from 'axios';

const LoginPage = () => {
	const { handleSubmit, register, formState } = useForm();
	const { errors } = formState;
	console.log(errors);
	const history = useHistory();

	const onLoginFormSubmit = (data) => {
		const myFormData = data;

		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/login',
				myFormData
			)
			.then((res) => {
				console.log(res);
				localStorage.setItem('ccAuth', true);
				history.push('/overview');
			})
			.catch((err) => console.log(err));
		console.log(myFormData);
		// alert(JSON.stringify(data));
	};
	return (
		<form className={classes.Form} onSubmit={handleSubmit(onLoginFormSubmit)}>
			<div className={classes.InputBox}>
				<label htmlFor="email">Email</label>
				<input
					type="email"
					name="email"
					id="email"
					placeholder="johndon@example.com"
					autoFocus
					{...register('email', {
						required: true,
					})}
				/>
				{errors.email && <p className={classes.ErrorMessage}>Please enter your email!</p>}
			</div>
			<div className={classes.InputBox}>
				<label htmlFor="password">Password</label>
				<input
					type="password"
					name="password"
					id="password"
					{...register('password', {
						required: true,
					})}
					placeholder="Enter your password"
				/>
				{errors.password && <p className={classes.ErrorMessage}>Please enter your password!</p>}
			</div>
			<button>Log In</button>
			<p className={classes.SignUpText}>
				Don't have an account yet?{' '}
				<Link to="/signup" className={classes.Link}>
					Sign Up Here
				</Link>
			</p>
		</form>
	);
};

export default LoginPage;
