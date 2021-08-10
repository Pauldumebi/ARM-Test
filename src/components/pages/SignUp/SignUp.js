import classes from './SignUp.module.css';

const SignUp = () => {
	return (
		<form className={classes.Form}>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="firstName">First Name</label>
					<input
						type="text"
						name="firstName"
						id="firstName"
						placeholder="John"
						autoFocus
						required
					/>
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="lastName">Last Name</label>
					<input type="text" name="lastName" id="lastName" placeholder="Doe" required />
				</div>
			</div>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="email">Email</label>
					<input type="email" name="email" id="email" placeholder="johndon@example.com" required />
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="address">Address</label>
					<input
						type="text"
						name="address"
						id="address"
						placeholder="123 Bowling Street"
						required
					/>
				</div>
			</div>
			<div className={classes.FlexContainer}>
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
				<div className={classes.InputBox}>
					<label htmlFor="confirmPassword">Confirm Password</label>
					<input
						type="password"
						name="confirmPassword"
						id="confirmPassword"
						required
						placeholder="Confirm password"
					/>
				</div>
			</div>
			<div className={classes.InputBox}>
				<label htmlFor="companyName">Company Name</label>
				<input
					type="text"
					name="companyName"
					id="companyName"
					required
					placeholder="Name of your Company"
				/>
			</div>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="companyTelephone">Company Telephone</label>
					<input
						type="number"
						name="companyTelephone"
						id="companyTelephone"
						placeholder="070XXXXXXXX"
						required
					/>
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="companyAddress">Company Address</label>
					<input
						type="text"
						name="companyAddress"
						id="companyAddress"
						placeholder="234 Silicon Street"
						required
					/>
				</div>
			</div>
			<button>Create Account</button>
		</form>
	);
};

export default SignUp;
