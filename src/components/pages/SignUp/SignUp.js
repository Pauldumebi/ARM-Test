import classes from './SignUp.module.css';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import axios from 'axios';
import * as Yup from 'yup';
import { useHistory } from 'react-router-dom';

// form validation rules
const validationSchema = Yup.object().shape({
	firstName: Yup.string().required('First Name is required'),
	lastName: Yup.string().required('Last name is required'),
	companyName: Yup.string().required('Company Name is required'),
	address: Yup.string().required('Company Address is required'),
	tel: Yup.string().required('Tel Number is required'),
	email: Yup.string().required('Email is required').email('Email is invalid'),
	password: Yup.string()
		.min(8, 'Password must be at least 6 characters')
		.required('Password is required'),
	confirmPassword: Yup.string()
		.oneOf([Yup.ref('password'), null], 'Passwords must match')
		.required('Confirm Password is required'),
});

const SignUp = () => {
	const formOptions = yupResolver(validationSchema);
	const history = useHistory();
	const { register, handleSubmit } = useForm(formOptions);

	const onSubmit = (data) => {
		const myFormData = data;
		delete myFormData.confirmPassword;
		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/signup',
				myFormData
			)
			.then((res) => {
				console.log(res);
				history.push('/overview');
			})
			.catch((err) => console.log(err));
		console.log(myFormData);
		// alert(JSON.stringify(data));
	};

	return (
		<form className={classes.Form} onSubmit={handleSubmit(onSubmit)}>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="firstName">First Name</label>
					<input
						type="text"
						name="firstName"
						id="firstName"
						placeholder="John"
						autoFocus
						{...register('firstName')}
					/>
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="lastName">Last Name</label>
					<input
						type="text"
						name="lastName"
						id="lastName"
						placeholder="Doe"
						{...register('lastName')}
					/>
				</div>
			</div>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="email">Email</label>
					<input
						type="email"
						name="email"
						id="email"
						placeholder="johndon@example.com"
						{...register('email')}
					/>
				</div>
				{/* <div className={classes.InputBox}>
					<label htmlFor="address">Address</label>
					<input type="text" name="address" id="address" placeholder="123 Bowling Street" />
				</div> */}
			</div>
			<div className={classes.FlexContainer}>
				<div className={classes.InputBox}>
					<label htmlFor="password">Password</label>
					<input
						type="password"
						name="password"
						id="password"
						placeholder="Enter your password"
						{...register('password')}
					/>
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="confirmPassword">Confirm Password</label>
					<input
						type="password"
						name="confirmPassword"
						id="confirmPassword"
						placeholder="Confirm password"
						{...register('confirmPassword')}
					/>
				</div>
			</div>
			<div className={classes.InputBox}>
				<label htmlFor="companyName">Company Name</label>
				<input
					type="text"
					name="companyName"
					id="companyName"
					{...register('companyName')}
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
						{...register('tel')}
					/>
				</div>
				<div className={classes.InputBox}>
					<label htmlFor="companyAddress">Company Address</label>
					<input
						type="text"
						name="companyAddress"
						id="companyAddress"
						placeholder="234 Silicon Street"
						{...register('companyAddress')}
					/>
				</div>
			</div>
			<button type="submit">Create Account</button>
		</form>
	);
};

export default SignUp;
