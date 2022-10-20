const user = { data: { loggedIn: null } };

/* backend end points */
const endPoints = { 
    signup: 'includes/signup.inc.php',
    login: 'includes/login.inc.php',
    logout: 'includes/logout.inc.php',
    loginState: 'includes/isloggedin.inc.php',
    verificationState: 'includes/isVerified.inc.php',
}

/* the forms array */
const forms = [
    {
        endPoint: endPoints.signup,
        form: document.getElementById('signup-form'),
        submit: document.querySelector('.signup-form .submit-button'),
        formFields: document.querySelectorAll('.signup-form .form-field'),
        isDirty: new Array(document.querySelectorAll('.signup-form .form-field').length).fill(false),
        defaultErrorMessages: [],
        dataSent: signupSuccess,
    },
    {
        endPoint: endPoints.login,
        form: document.getElementById('login-form'),
        submit: document.querySelector('.login-form .submit-button'),
        formFields: document.querySelectorAll('.login-form .form-field'),
        isDirty: new Array(document.querySelectorAll('.login-form .form-field').length).fill(false),
        defaultErrorMessages: [],
        dataSent: loginSuccess,
    },
    {
        endPoint: endPoints.logout,
        form: document.getElementById('logout-form'),
        submit: document.querySelector('.logout-form .submit-button'),
        formFields: [],
        isDirty: [],
        defaultErrorMessages: [],
        dataSent: logoutSuccess,
    },
];

/* data sent notification element */
const dataSentMsg = document.querySelector('.form-data-sent');


initAuth();


function initAuth() {
    window.addEventListener('loginchange', loginStateListener); //listen for login state change
    checkUserLoggedIn();                                        //determine if a user is already logged in
    addHideDataSentMessageListeners();                          //add event listeners to the notification modal
    forms.forEach((form, index) => {
        form.formFields.forEach(ff => form.defaultErrorMessages.push(ff.nextElementSibling.textContent.replace(/[\n\r]/g, ''))); //save default hints
        addFormFieldListeners(form);                                                        //form fields check valid data  
        form.submit.addEventListener('click', submitPreflightListener.bind(null, form));    //submit button clicked, check valid form data
        form.form.addEventListener('submit', submitListener.bind(null, form, index));       //on submit send form data to the end point
    });
    addNonSubmitButtonListeners();
    document.querySelector('.fade-in').style.opacity = '1';     //let the document's body fade in
}


/* event listeners, let the sent notification fade out */
function addHideDataSentMessageListeners() {
    ['click', 'keyup', 'touchstart'].forEach(ev => dataSentMsg.addEventListener(ev, e => {
        dataSentMsg.style.opacity = '0';
        setTimeout(() => dataSentMsg.style.display = 'none', 400);
    }));
}


/* add event listers for non submit buttons */
function addNonSubmitButtonListeners() {
    document.getElementById('signup-button').addEventListener('click', signupButtonListener);   //on click show signup form
    document.getElementById('login-button').addEventListener('click', loginButtonListener);     //on click show login form
    document.getElementById('guest-button').addEventListener('click', guestButtonListener);     //on click guest login
}


/* event listeners to check whether invalid 
   data has been entered into a form field */
function addFormFieldListeners(form) {
    form.formFields.forEach((ff, i) => {
        ['blur', 'keyup'].forEach(ev => ff.addEventListener(ev, e => {
            ff.nextElementSibling.textContent = form.defaultErrorMessages[i];
            if (e.type == 'blur') {
                form.isDirty[i] = ff.value ? true : false;
            }
            !ff.value || !form.isDirty[i] ? ff.classList.toggle('invalid', false) : ff.classList.toggle('invalid', !ff.checkValidity());
        }));
    });
}


/* event listener, on click login as Guest */
function guestButtonListener(e) {
    const index = forms.findIndex(form => form.endPoint == endPoints.login);
    const fields = [...(forms[index] || {}).formFields || []];
    if (index > -1) {
        clearFormData(index);
        fields[fields.findIndex(f => f.name == 'uid')].value = 'Guest';
        fields[fields.findIndex(f => f.name == 'pwd')].value = '123456';
        document.getElementById('login-submit').focus();
    }
}


/* event listener, on click hide login + show signup form */
function signupButtonListener(e) {
    forms.forEach((f, i) => clearFormData(i));
    document.getElementById('signup-container').style.display = 'block';
    setTimeout(() => document.getElementById('signup-container').style.opacity = '1', 150);
    document.getElementById('login-container').style = 'opacity: 0; display: none;';
    window.scroll({top: 0, left: 0, behavior: "smooth"});
}


/* event listener, on click hide signup + show login form */
function loginButtonListener(e) {
    forms.forEach((f, i) => clearFormData(i));
    document.getElementById('login-container').style.display = 'block';
    setTimeout(() => document.getElementById('login-container').style.opacity = '1', 150);
    document.getElementById('signup-container').style = 'opacity: 0; display: none;';
    setTimeout(() => window.scroll({top: 0, left: 0, behavior: "smooth"}), 150);
}


/* event listener, on login state change */
function loginStateListener(e) {
    user.data = e.detail.loginState;
    console.log('login state: ', e.detail.loginState);
}


/* event listener, on submit button clicked, check
   if all required data has been entered correctly */
function submitPreflightListener(form, e) {
    let invalidField = null;
    form.formFields.forEach(ff => {
        invalidField = ff.required ? (ff.checkValidity() ? invalidField : invalidField ? invalidField : ff) : invalidField;
        ff.classList.toggle('invalid', !ff.checkValidity());
    });
    if (invalidField) {
        e.preventDefault();
        invalidField.focus();
    }
}


/* event listener, on submit send form data to the endpoint*/
function submitListener(form, index, e) {
    e.preventDefault();
    const formData = new FormData(form.form);
    const formDataObject = Object.fromEntries(formData);
    submitRequest(form.endPoint, formDataObject)
        .then(result => {
            if (result.ok) {
                form.dataSent(result, formDataObject);
                clearFormData(index);
            } else {
                reportInvalidFormData(index, result);
                console.log('result: ', result);
            }
        });
}


/* send request to the endpoint */
async function submitRequest(endPoint, dataObject) {
    try {
        const response = await fetch(endPoint, {
            method: 'POST',
            body: JSON.stringify(dataObject),
            headers: { 'Content-Type': 'application/json' }
        });
        return await response.json();
    } catch (err) {
        console.error(err);
        return { err: err, ok: false, data: {} };
    }
}


/* initialize form data and show notification */
function signupSuccess(result, loginData) {
    dataSentMsg.style.display = 'flex';
    dataSentMsg.style.opacity = '1';
    dataSentMsg.focus();
    login(loginData);
    window.scroll({top: 0, left: 0, behavior: "smooth"});
}


/* initialize form data, show logout button, hide login + signup form */
function loginSuccess(result, loginData) {
    triggerLoginChangeEvent(result);
    document.getElementById('login-container').style = 'opacity: 0; display: none;';
    document.getElementById('signup-container').style = 'opacity: 0; display: none;';
    document.getElementById('logout-container').style = 'display: block; opacity: 1;';
    document.querySelector('header').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('hello-message').innerHTML = `Hello <span class="fullname">${user.data.userName},</span> you are logged in as <span class="uname">${user.data.userId}.</span>`;
        document.getElementById('verified-message').innerHTML = user.data.userVerified ? 'verified account' : 'Your account has not yet been verified';
        document.querySelector('header').style.opacity = '1';
    }, 150);
    window.scroll({top: 0, left: 0, behavior: "smooth"});
}


/* initialize form data, hide logout button, show login + signup form */
function logoutSuccess(result, loginData) {
    triggerLoginChangeEvent(result);
    document.getElementById('login-container').style = 'display: block; opacity: 1;';
    document.getElementById('signup-container').style = 'opacity: 0; display: none;';    
    document.getElementById('logout-container').style = 'opacity: 0; display: none;';
    document.querySelector('header').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('hello-message').innerHTML = 'You are not logged in.';
        document.getElementById('verified-message').innerHTML = '&nbsp;';
        document.querySelector('header').style.opacity = '1';
    }, 150);
    window.scroll({top: 0, left: 0, behavior: "smooth"});
}


/* dispatch an event on login state change */
function triggerLoginChangeEvent(result) {
    if (result.ok && result.data.loggedIn != user.data.loggedIn) {
        const loginChange = new CustomEvent('loginchange', {
            detail: { loginState: result.data },
            bubbles: true,
            cancelable: true,
            composed: false,
        });
        window.dispatchEvent(loginChange);
    }
}


/* check which fields were invalid 
   and focus the first invalid field */
function reportInvalidFormData(i, result) {
    if (!result.fields) {
        clearFormData(i);
    } else {
        let invalidField = null;
        forms[i].formFields.forEach(ff => invalidField = checkForInvalid(ff, result, invalidField));
        if (invalidField) invalidField.focus();
    }
}


/* set the hint to the status message reported by 
   the backend, determine which field to focus */
function checkForInvalid(ff, result, invalidField) {
    if (result.fields.includes(ff.name)) {
        ff.classList.toggle('invalid', true);
        ff.nextElementSibling.textContent = result.err;
        invalidField = invalidField ? invalidField : ff;
    }
    return invalidField;
}


/* initialize form data */
function clearFormData(i) {
    forms[i].formFields.forEach((ff, j) => {
        ff.value = '';
        forms[i].isDirty[j] = false;
        ff.nextElementSibling.textContent = forms[i].defaultErrorMessages[j];
        ff.classList.toggle('invalid', false);
    });
}


/* determine if a user is already logged in */
async function checkUserLoggedIn() {
    return await submitRequest(endPoints.loginState, {})
        .then(result => {
            result.ok && result.data.loggedIn ? loginSuccess(result) : logoutSuccess(result);
            /* just for demonstration purpose */
            document.getElementById('state-container').style.display = 'block';
        });
}


/* determine if a user's account is verified */
async function checkUserVerified() {
    if (user.data.loggedIn) {
        return await submitRequest(endPoints.verificationState, { uid: user.data.userId, email: user.data.userEmail, bcc: "" })
            .then(result => {
                if (result.ok && result.data.loggedIn) {
                    user.data.userVerified = result.data.userVerified;
                    result.data = user.data;
                    loginSuccess(result)
                } else {
                    result.data.loggedIn = false;
                    logoutSuccess(result);
                }
            });
    }
}


/* user login */
async function login(loginData) {
    return await submitRequest(endPoints.login, loginData)
        .then(result => result.ok ? loginSuccess(result) : logoutSuccess(result));
}


/* user logout */
async function logout() {
    return await submitRequest(endPoints.logout, {})
        .then(result => logoutSuccess(result));
}


export { login, logout, checkUserLoggedIn, user }