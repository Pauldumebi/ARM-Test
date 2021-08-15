import { Link, useHistory } from 'react-router-dom';
import classes from './Login.module.css';
import { useForm } from 'react-hook-form';
import axios from 'axios';
import { useState } from 'react';
import Loader from '../../layout/Loader/Loader';

const LoginPage = () => {
	const { handleSubmit, register, formState } = useForm();
	const { errors } = formState;
	const [loading, setLoading] = useState(false);

	const history = useHistory();

	const onLoginFormSubmit = (data) => {
		const myFormData = data;
		setLoading(true);

		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/login',
				myFormData
			)
			.then((res) => {
				console.log(res);
				const userInfo = JSON.stringify({
					firstName: res.data.userData.firstname,
					role: res.data.userData.role,
					companyID: res.data.userData.companyID,
					companyName: res.data.userData.companyName,
					userID: res.data.userData.id,
				});

				localStorage.setItem('ccAuth', userInfo);
				setLoading(false);
				history.push('/overview');
			})
			.catch((err) => console.log(err));

		// alert(JSON.stringify(data));
	};
	return (
		<form className={classes.Form} onSubmit={handleSubmit(onLoginFormSubmit)} autoComplete="off">
			<div className={classes.InputBox}>
				<label htmlFor="email">Email</label>
				<input
					type="email"
					name="email"
					id="email"
					placeholder="johndon@example.com"
					autoFocus
					autoComplete="false"
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
					autoComplete="false"
					{...register('password', {
						required: true,
					})}
					placeholder="Enter your password"
				/>
				{errors.password && <p className={classes.ErrorMessage}>Please enter your password!</p>}
			</div>
			{loading ? <Loader /> : <button type="submit">Log In</button>}
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
