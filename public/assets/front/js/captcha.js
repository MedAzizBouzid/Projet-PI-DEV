let captcha = document.getElementById("captcha");
console.log("test");

inputField = document.getElementById("2");
let allCharacters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
                     'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
                     'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
                     't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
function getCaptcha() {
  for (let i = 0; i < 6; i++) {
    //getting 6 random characters from the array
    let randomCharacter =
      allCharacters[Math.floor(Math.random() * allCharacters.length)];
    captcha.innerHTML += ` ${randomCharacter}`; //passing 6 random characters inside captcha innerText
  }
}
getCaptcha();
let myForm = document.querySelector(".env");
myForm.addEventListener("submit", (e) => {
/**********************************captcha******************************** */

statusTxt = document.querySelector(".status-text");

statusTxt.style.display = "block";
let inputVal = inputField.value.split('').join(' ');
if(inputVal == captcha.innerText){ 
  statusTxt.style.color = "#4db2ec";
  statusTxt.innerText = "Nice! You don't appear to be a robot.";
  
}else{
  statusTxt.style.color = "#ff0000";
  statusTxt.innerText = "Captcha not matched. Please try again!";
  e.preventDefault(); 
  setTimeout(()=>{ 
      removeContent();
      getCaptcha();
    }, 2000);
}

});
function removeContent(){
  inputField.value = "";
  captcha.innerText = "";
  statusTxt.style.display = "none";
 }