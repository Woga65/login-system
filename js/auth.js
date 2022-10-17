const user = { data: {} };

/* the forms array */
const forms = [
    {
        endPoint: 'includes/signup.inc.php',
        form: document.getElementById('signup-form'),
        submit: document.querySelector('.signup-form .submit-button'),
        formFields: document.querySelectorAll('.signup-form .form-field'),
        isDirty: new Array(document.querySelectorAll('.signup-form .form-field').length).fill(false),
        defaultErrorMessages: [],
        dataSent: signupSuccess,
    },
    {
        endPoint: 'includes/login.inc.php',
        form: document.getElementById('login-form'),
        submit: document.querySelector('.login-form .submit-button'),
        formFields: document.querySelectorAll('.login-form .form-field'),
        isDirty: new Array(document.querySelectorAll('.login-form .form-field').length).fill(false),
        defaultErrorMessages: [],
        dataSent: loginSuccess,
    },
    {
        endPoint: 'includes/logout.inc.php',
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
    checkUserLoggedIn();                                        //determine if a user is already logged in
    document.querySelector('.fade-in').style.opacity = '1';     //let the document's body fade in
    addHideDataSentMessageListeners();
    forms.forEach((form, index) => {
        form.formFields.forEach(ff => form.defaultErrorMessages.push(ff.nextElementSibling.textContent.replace(/[\n\r]/g, ''))); //save default hints
        addFormFieldListeners(form);                                                        //form fields check valid data  
        form.submit.addEventListener('click', submitPreflightListener.bind(null, form));    //submit button clicked, check valid form data
        form.form.addEventListener('submit', submitListener.bind(null, form, index));       //on submit send form data to the end point
    });
}


/* event listeners, let the sent notification fade out */
function addHideDataSentMessageListeners() {
    ['click', 'keyup', 'touchstart'].forEach(ev => dataSentMsg.addEventListener(ev, e => {
        dataSentMsg.style.opacity = '0';
        setTimeout(() => dataSentMsg.style.display = 'none', 400);
    }));
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
            console.log('result: ', result);
            if (result.ok) {
                form.dataSent(result);
                clearFormData(index);
            } else {
                reportInvalidFormData(index, result);
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
function signupSuccess(result) {
    dataSentMsg.style.display = 'flex';
    dataSentMsg.style.opacity = '1';
    dataSentMsg.focus();
}


/* initialize form data, show logout button, hide login + signup form */
function loginSuccess(result) {
    user.data = result.data;
    document.getElementById('login-container').style.display = 'none';
    document.getElementById('signup-container').style.display = 'none';
    document.getElementById('logout-container').style.display = 'block';
    document.getElementById('hello-message').innerHTML = `Hello <span class="fullname">${user.data.userName},</span> you are logged in as <span class="uname">${user.data.userId}.</span>`;
    document.getElementById('verified-message').innerHTML = user.data.userVerified ? 'verified account' : 'Your account has not yet been verified';
}


/* initialize form data, hide logout button, show login + signup form */
function logoutSuccess(result) {
    user.data = result.data;
    document.getElementById('login-container').style.display = 'block';
    document.getElementById('signup-container').style.display = 'block';
    document.getElementById('logout-container').style.display = 'none';
    document.getElementById('hello-message').innerHTML = 'You are not logged in.';
    document.getElementById('verified-message').innerHTML = '';
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
    });
}


/* determine if a user is already logged in */
async function checkUserLoggedIn() {
    return await submitRequest('includes/isloggedin.inc.php', {})
        .then(result => {
            console.log('result: ', result);
            result.ok && result.data.loggedIn ? loginSuccess(result) : logoutSuccess(result);
            /* just for demonstration purpose */
            document.getElementById('state-container').style.display = 'block';
        });
}


export { checkUserLoggedIn, user }