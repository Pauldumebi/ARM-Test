import { Route, Switch } from 'react-router-dom';
import './App.css';
import Navbar from './components/layout/Navbar/Navbar';
import Courses from './components/pages/Courses/Courses';
import LandingPage from './components/pages/LandingPage/LandingPage';
import Login from './components/pages/Login/Login';
import SignUp from './components/pages/SignUp/SignUp';

function App() {
	return (
		<div className="App">
			<Navbar />
			<Switch>
				<Route exact path="/" component={LandingPage} />
				<Route exact path="/login" component={Login} />
				<Route exact path="/signup" component={SignUp} />
				<Route exact path="/courses" component={Courses} />
			</Switch>
		</div>
	);
}

export default App;
