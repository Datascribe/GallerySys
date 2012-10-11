
function Dollar (val) {  // force to valid dollar amount
var str,pos,rnd=0;
  if (val < .995) rnd = 1;  // for old Netscape browsers
  str = escape (val*1.0 + 0.005001 + rnd);  // float, round, escape
  pos = str.indexOf (".");
  if (pos > 0) str = str.substring (rnd, pos + 3);
  return str;
}

function ReadForm (obj1) { // process selects
var i,j,amt=0,des="",obj,pos,tok,val,
    op1a="",op1b="",op2a="",op2b="",itmn="";
var ary = new Array ();
  if (obj1.baseamt) amt  = obj1.baseamt.value*1.0;  // base amount
  if (obj1.basedes) des  = obj1.basedes.value;  // base description
  if (obj1.baseon0) op1a = obj1.baseon0.value;  // base options
  if (obj1.baseos0) op1b = obj1.baseos0.value;
  if (obj1.baseon1) op2a = obj1.baseon1.value;
  if (obj1.baseos1) op2b = obj1.baseos1.value;
  if (obj1.baseitn) itmn = obj1.baseitn.value;
  for (i=0; i<obj1.length; i++) {     // run entire form
    obj = obj1.elements[i];           // a form element
    if (obj.type == "select-one") {   // just get selects
      if (obj.name == "quantity" ||   // don't mess with these
          obj.name == "amount") continue;
      pos = obj.selectedIndex;        // which option selected
      val = obj.options[pos].value;   // selected value
      ary = val.split (" ");          // break apart
      for (j=0; j<ary.length; j++) {  // look at all items
// first we do single character tokens...
        if (ary[j].length < 2) continue;
        tok = ary[j].substring (0,1); // first character
        val = ary[j].substring (1);   // get data
        if (tok == "@") amt = val * 1.0;
        if (tok == "+") amt = amt + val*1.0;
        if (tok == "%") amt = amt + (amt * val/100.0);
        if (tok == "#") {             // record item number
          if (obj1.item_number) obj1.item_number.value = val;
          ary[j] = "";                // zap this array element
        }
// Now we do 3-character tokens...
        if (ary[j].length < 4) continue;
        tok = ary[j].substring (0,3); // first 3 chars
        val = ary[j].substring (3);   // get data
        if (tok == "s1=") {           // value for shipping
          if (obj1.shipping)  obj1.shipping.value  = val;
          ary[j] = "";                // clear it out
        }
        if (tok == "s2=") {           // value for shipping2
          if (obj1.shipping2) obj1.shipping2.value = val;
          ary[j] = "";                // clear it out
        }
      }
      val = ary.join (" ");           // rebuild val with what's left

      if (obj.name == "on0" ||        // let these go where they want
          obj.name == "os0" ||
          obj.name == "on1" ||
          obj.name == "os1") continue;

      tag = obj.name.substring (obj.name.length-2);  // get flag
      if      (tag == "1a") op1a = op1a + " " + val; // stuff data
      else if (tag == "1b") op1b = op1b + " " + val;
      else if (tag == "2a") op2a = op2a + " " + val;
      else if (tag == "2b") op2b = op2b + " " + val;
      else if (tag == "3i") itmn = itmn + " " + val;
      else if (des.length == 0) des = val;
      else des = des + ", " + val;
    }
  }
// Now summarize stuff we just processed, above
  if (op1a.length > 0) obj1.on0.value = op1a;  // stuff it away
  if (op1b.length > 0) obj1.os0.value = op1b;
  if (op2a.length > 0) obj1.on1.value = op2a;
  if (op2b.length > 0) obj1.os1.value = op2b;
  if (itmn.length > 0) obj1.item_number.value = itmn;
  obj1.item_name.value = des;
  obj1.amount.value = Dollar (amt);
  if (obj1.tot) obj1.tot.value = "£" + Dollar (amt);
}
