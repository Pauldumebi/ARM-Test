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
		</form>
	);
};

export default LoginPage;
