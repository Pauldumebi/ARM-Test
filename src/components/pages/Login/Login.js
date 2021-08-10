import { Link } from 'react-router-dom';
import classes from './Login.module.css';

const LoginPage = () => {
	return (
		<form className={classes.Form}>
			<div className={classes.InputBox}>
				<label htmlFor="email">Email</label>
				<input
					type="email"
					name="email"
					id="email"
					placeholder="johndon@example.com"
					autoFocus
					required
				/>
			</div>
			<div className={classes.InputBox}>
				<label htmlFor="password">Password</label>
				<input
					type="password"
					name="password"
					id="password"
					required
					placeholder="Enter your password"
				/>
			</div>
			<button>Log In</button>
			<p>
				Don't have an account yet?{' '}
				<Link to="/signup" className={classes.Link}>
					Sign Up Here
				</Link>
			</p>
		</form>
	);
};

export default LoginPage;
