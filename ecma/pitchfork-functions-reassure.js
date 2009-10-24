/*Reassurance javascript*/

/*Quick and dirty way of telling the user that the system is actually doing something.*/

function pitchfork_action_displayReassurance(message)
{var workingbox = document.getElementById('div-msg-reassure');
 workingbox.style.display="block";
 workingbox.innerHTML = message;}

function pitchfork_action_hideReassurance()
{var workingbox = document.getElementById('div-msg-reassure');
 workingbox.style.display="none";}
 
function pitchfork_action_addWorkingListeners()
{var anchorTags = document.getElementsByTagName('a');
 var formTags = document.getElementsByTagName('form');
 
 for (var i=0; i<formTags.length; i++)
 {var formSubmit = formTags[i].getAttribute('onsubmit');
  
  /*If no onsubmit attribute has been set...*/
  if(formSubmit=="") /*Then add one!*/
  {formTags[i].setAttribute('onsubmit','displayWorking("Processing form...")');}}
  
 for(var i=0; i<anchorTags.length; i++)
 {var anchorClick = anchorTags[i].getAttribute('onclick');
 
   if(anchorClick=="")
   {anchorTags[i].setAttribute('onclick','displayWorking("Loading page...")');}}}

/*Aliases for these functions (less typing)*/
function displayWorking(message)
{pitchfork_action_displayReassurance(message);}

function eraseWorking()
{pitchfork_action_hideReassurance();}

function hideWorking()
{pitchfork_action_hideReassurance();}

function addWorkingListeners()
{pitchfork_action_addWorkingListeners();}