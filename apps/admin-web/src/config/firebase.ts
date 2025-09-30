import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider } from 'firebase/auth';

const firebaseConfig = {
  apiKey: 'AIzaSyA8Oq4osjN9gEgknx_AxVocddUcuoDTC90',
  authDomain: 'neibrpay-hoa.firebaseapp.com',
  projectId: 'neibrpay-hoa',
  storageBucket: 'neibrpay-hoa.firebasestorage.app',
  messagingSenderId: '351640228155',
  appId: '1:351640228155:web:2272424672aaf5e8a2a71e',
  measurementId: 'G-DB646XGZ88',
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase Authentication and get a reference to the service
export const auth = getAuth(app);

// Initialize Google Auth Provider
export const googleProvider = new GoogleAuthProvider();

// Configure Google provider
googleProvider.addScope('email');
googleProvider.addScope('profile');

export default app;
