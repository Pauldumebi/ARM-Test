import * as actionTypes from '../actions/actionTypes';
import { findItem, totalCost } from '../utils';

// IMPORTANT!!!!!!!!!!!!!!!!!!!
// TO CHECKOUT FIELD IS FOR BETTER UX, IF TO CHECKOUT IS MARKED TRUE BEFORE LOGGING IN
// USER IS REDIRECTED TO CHECKOUT AFTER LOGGING IN

export const initialState = {
	cart: [],
	total: 0,
	toCheckout: false,
	allCourses: [],
};

export const reducer = (state, action) => {
	switch (action.type) {
		case actionTypes.ADD_TO_CART: {
			const allitems = [...state.allCourses];
			const selectedItem = findItem(allitems, action.courseID);
			const updatedCart = [...state.cart, ...selectedItem];
			const updatedTotal = totalCost(updatedCart);
			console.log(updatedCart, updatedTotal);
			return {
				...state,
				cart: updatedCart,
				total: updatedTotal,
			};
		}
		case actionTypes.TO_CHECKOUT: {
			return {
				...state,
				toCheckout: true,
			};
		}
		case actionTypes.SET_ALL_COURSES: {
			console.log(action.courses);
			return {
				...state,
				allCourses: action.courses,
			};
		}

		default:
			return state;
	}
};
