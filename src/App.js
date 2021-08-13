import { Redirect, Route, Switch, useLocation } from 'react-router-dom';
import './App.css';
import React, { useState } from 'react';
import Navbar from './components/layout/Navbar/Navbar';
import Courses from './components/pages/Courses/Courses';
import LandingPage from './components/pages/LandingPage/LandingPage';
import Login from './components/pages/Login/Login';
import Overview from './components/pages/Overview/Overview';
import SignUp from './components/pages/SignUp/SignUp';
import SingleCourse from './components/pages/SingleCourse/SingleCourse';

function App() {
	const auth = localStorage.getItem('ccAuth');

	const [isAuth, setIsAuth] = useState(auth);
	const { pathname } = useLocation();

	React.useEffect(() => {
		const auth = localStorage.getItem('ccAuth');
		setIsAuth(auth);
	}, [pathname]);
	return (
		<div className="App">
			<Navbar />
			<Switch>
				<Route exact path="/" component={LandingPage} />
				{!isAuth && <Route exact path="/login" component={Login} />}
				{!isAuth && <Route exact path="/signup" component={SignUp} />}
				<Route exact path="/courses" component={Courses} />
				<Route exact path="/overview" component={Overview} />
				<Route exact path="/courses/:courseName" component={SingleCourse} />
				<Redirect to="/" />
			</Switch>
		</div>
	);
}

export default App;
