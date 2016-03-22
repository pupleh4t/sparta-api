/**
 * Created by root on 22/03/16.
 */
google.load('maps','3', {other_params: "sensor=false"});

var query='';
var lats = new Array(10);
var lons = new Array(10);
var geocoder;
var f1;
var customIndex=0;
var sigDigits=8;
var mapwin = null;
var gStartlat=0;
var gStartlon=0;
var point = new Object();
point.lat = 0;
point.lon = 0;
point.finalBrg=0;
point.backBrg=0;
var smajor= new Array(6372797.6, 6378137, 6377340.189, 6378160, 6377397.155, 6378206.4, 6378249.145, 6377276.345, 6378137, 6378388, 6378245, 6378160, 6378135, 0);
var flat= new Array(0, 298.257223563, 299.3249646, 298.25, 299.1528128, 294.9786982138, 293.465, 300.8017, 298.257222101, 297, 298.3, 298.25, 298.26, 0);


function initialize() {
    f1=document.frm;
    customIndex=f1.model.length-1;
    f1.results.value = "";
    geocoder=new google.maps.Geocoder();
    f1.search.focus();
}


function launchGeocode() {
    query=trim(document.getElementById('search').value);
    var l=query.length;
    if (l==0)
    {
        alert("You must enter a city, town or other place to search for");
        f1.search.focus();
        return;
    }
    geocoder.geocode({'address': query}, gCallback);
}

function gCallback(res, status) {
    var r=f1.results;
    if (status != "OK") {
        switch(status) {
            case "ZERO_RESULTS":
                alert('No results were found for this location');
                break;
            case "OVER_QUERY_LIMIT":
                alert('Google geocoder speed limit.');
                break;
            case "REQUEST_DENIED":
                alert('Request denied');
                break;
            case "INVALID_REQUEST":
                alert('Invalid request');
                break;
        }
        r.length=0;
        return;
    }
// status=OK
    var addr;
    r.length=0;
    lats.length=0;
    lons.length=0;
    for (i=0; i<res.length; i++) {
        addr=res[i].formatted_address;
        appendOptionLast("results", addr);
        r[r.length-1].value=res[i].geometry.location.lat() + "|" + res[i].geometry.location.lng();
        lats[i] = res[i].geometry.location.lat();
        lons[i] = res[i].geometry.location.lng();
    }
    if (lats.length > 0) {
        f1.startlat.value=lats[0];
        f1.startlon.value=lons[0];
    }
    if (r.length == 1) {
        document.getElementById("resultslabel").innerHTML = "1 search result:";
    } else {
        document.getElementById("resultslabel").innerHTML = r.length + " search results:";
    }
    toggle2Rows("resultsrow", "searchrow");
    f1.results.focus();
}

function toggle2Rows(div1, div2) {
    document.getElementById(div1).style.display = "block";
    document.getElementById(div2).style.display = "none";
}


document.write('<form NAME="frm" id="frm" method="post" action="viewmap.php" style="width: 100%"><table cellpadding="3"><tr><td id="searchrow" align="left" style="width: 100%"><label for="search" accesskey="S">Search for place:</label><input type="text" id="search" value="" size="30" maxlength="52" onfocus="this.select">&nbsp;&nbsp;<input type="button" class="btn" value="Find it" onClick="launchGeocode()"></td><td id="resultsrow" align="left" style="width: 100%; display: none"><label id="resultslabel" for="results" accesskey="U" style="background-color: #7FFFD4; border: 1px black solid">0 search results:</label><select id="results" style="width: 14.1em" onchange="writeLatLon()"><option></option></select>&nbsp;&nbsp;<input type="button" class="btn" value="New search" onclick="clearGeocode()"></td></tr></table><table cellpadding="3"><tr id="startlatrow"><td align="left" colspan="2"><label for="startlat" accesskey="L">Starting point:</label><br><label for="startlat" accesskey="L">Latitude:</label><input type="text" id="startlat" value="" size="14" maxlength="14" onfocus="this.select()">&nbsp;&nbsp;<label for="startlon" accesskey="N">Longitude:</label><input type="text" id="startlon" value="" size="14" maxlength="14" onfocus="this.select()"></td></tr><tr id="maxdistrow"><td align="left" colspan="2"><label for="bearing" accesskey="B">Bearing (0-360):</label><input type="text" id="bearing" value="" size="14" maxlength="14" onfocus="this.select()">&nbsp;&nbsp;&nbsp;<label for="maxdist" accesskey="I">Distance:</label><input type="text" id="dist" value="" size="14" maxlength="14" onfocus="this.select()">&nbsp;<input type="radio" name="distunits" checked>miles&nbsp;<input type="radio" name="distunits">km</td></tr><tr id="ellipserow" style="display: none"><td align="left" colspan="2"><label for="major">Ellipsoid size (in meters):</label><br><label for="major">Equatorial radius:</label><input type="text" id="major" value="" size="14" maxlength="14" onfocus="this.select()">&nbsp;&nbsp;&nbsp;<select id="param2"><option selected>&nbsp;Polar radius:<option>Inv flattening:</select><input type="text" id="minor" value="" size="14" maxlength="14" onfocus="this.select()"></td></tr><tr><td align="left" colspan="2">&nbsp;&nbsp;<input type="button" class="btn" value="Calculate" accesskey="C" onclick="calculate()">&nbsp;&nbsp;<input type="button" class="btn" value="Clear" accesskey="0" onclick="resetValues()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="model" accesskey="M">Earth model:</label><select id="model" onchange="setEllipse()"><option>Sphere<option selected>WGS84 ellipsoid<option>Airy modified ellipsoid<option>Australian ellipsoid<option>Bessel (1841) ellipsoid<option>Clarke (1866)/NAD27 ellipsoid<option>Clarke (1880) ellipsoid<option>Everest (1830) ellipsoid<option>GRS80 ellipsoid<option>International/Hayford ellipsoid<option>Krasovsky ellipsoid<option>South American (1969 ellipsoid<option>WGS72 ellipsoid<option>User defined ellipsoid</select></td></tr><tr><td align="left"><label for="destination" id="destinationlabel" accesskey="P">Destination point:</label><br><textarea class="results" id="destination" cols="52" rows="4" readonly="readonly" onfocus="this.select()" onclick="this.select()"></textarea></td><td align="left" valign="top"><br>&nbsp;<input type="button" class="btn" value="See it on map" Id="map" onClick="viewMap()"><br>&nbsp;<i style="font-size:88%">Courtesy of Google Maps</i></td></tr></table></form>');


function writeLatLon() {
    var i = f1.results.selectedIndex;
    f1.startlat.value = lats[i];
    f1.startlon.value = lons[i];
}

function setEllipse() {
    var i=f1.model.selectedIndex;
    if (i==customIndex) {
        f1.major.value="";
        f1.minor.value="";
        f1.destination.value="";
        document.getElementById("ellipserow").style.display = "block";
        return;
    } else {
        document.getElementById("ellipserow").style.display = "none";
        if (f1.destination.value != "") {
            calculate();
        }
    }
}

function appendOptionLast(combo, item) {
    var elOptNew = document.createElement('option');
    elOptNew.text = item;
    elOptNew.value = item;
    var elSel = document.getElementById(combo);
    try {
        elSel.add(elOptNew, null);
    }
    catch(ex) {
        elSel.add(elOptNew);
    }
}


function clearGeocode() {
    f1.search.value = "";
    f1.results.length=0;
    document.getElementById("resultslabel").innerHTML = "search results:";
    f1.startlat.value = "";
    f1.startlon.value = "";
    toggle2Rows("searchrow", "resultsrow");
    f1.destination.value= "";
    f1.search.focus()
}

function resetValues() {
    f1.startlat.value="";
    f1.startlon.value="";
    f1.bearing.value = "";
    f1.dist.value="";
    f1.search.value = "";
    f1.results.length = 0;
    f1.destination.value= "";
    f1.major.value="";
    f1.minor.value="";
    document.getElementById("searchrow").style.display = "block";
    document.getElementById("resultsrow").style.display = "none";
}

function rad(dg) {
    return (dg* Math.PI / 180);
}

function deg(rd) {
    return (rd* 180 / Math.PI);
}

function trim(s) {
    while(s.charCodeAt(0)<33 || s.charCodeAt(0)==160)
        s=s.substring(1,s.length);
    while(s.charCodeAt(s.length-1)<33 || s.charCodeAt(s.length-1)==160)
        s=s.substring(0,s.length-1);
    return s;
}

function normalizeLongitude(lon) {
    var n=Math.PI;
    if (lon > n) {
        lon = lon - 2*n
    } else if (lon < -n) {
        lon = lon + 2*n
    }
    return lon;
}

function normalizeBearing(brg) {
    var n=2*Math.PI;
    if (brg < 0) {
        brg = brg + n
    } else if (brg >= n) {
        brg = brg - n
    }
    return brg;
}

function isValidFP(n, name, allowNull) {
    if (n == "" && !allowNull) {
        alert("The " + name + " is required");
        return false;
    }
    if (parseFloat(n, 10) != n) {
        alert("The " + name + " is invalid");
        return false;
    }
    return true;
}

function checkMinMax(n, mn, mx, name, units) {
    if (n<mn || n>mx) {
        alert("The " + name + " must be between " + mn + " and " + mx + " " + units);
        return false
    }
    return true;
}

function calculate() {
    var mx, units, radiusEarth, smaj;
    var brg = new Array(0, 180, 0);
    with (Math) {
        var startlat=latLonToDecimal(f1.startlat.value, -90, 90, "latitude");
        if (startlat == -999) {
            f1.startlat.focus();
            return;
        }
        gStartlat=startlat;
        var j=0;
        if (startlat == 90) {
            startlat = 89.999999999;
            j=1
        }
        if (startlat == -90) {
            startlat = -89.999999999;
            j=2;
        }
        startlat=rad(startlat);
        var startlon=latLonToDecimal(f1.startlon.value, -180, 180, "longitude");
        if (startlon == -999) {
            f1.startlon.focus();
            return;
        }
        gStartlon=startlon;
        startlon=rad(startlon);
        brg[0] = latLonToDecimal(f1.bearing.value, 0, 360, "bearing");
        if (brg[0] == -999) {
            f1.bearing.focus();
            return;
        }
        brg[j] = rad(brg[j]);
        var dist=f1.dist.value;
        if (!isValidFP(dist, "distance", false)) {
            f1.dist.focus();
            return false;
        }
        if (f1.distunits[1].checked) {
            mx=41000;
            radiusEarth=6372.7976;
            units="km";
        } else {
            mx=25000;
            radiusEarth=3959.8728;
            units="miles";
        }
        var i=f1.model.selectedIndex;
        if (i<customIndex) {
// models 0 to 12
            smaj=smajor[i]*1;
            if (!checkMinMax(dist, 0, mx, "distance", units)) {
                f1.dist.focus();
                return false;
            }
            var sminor=getSemiMinor(smaj, flat[i]);
        } else {
// user defined ellipse
            smajor[i]= trim(f1.major.value);
            smaj= f1.major.value*1;
            if (!isValidFP(smaj, "equatorial radius", false) || !checkMinMax(smaj, 1, 1000000000, "equatorial radius", "meters")) {
                f1.major.focus();
                return false;
            }
            var sminor= trim(f1.minor.value)*1;
            if (f1.param2.selectedIndex == 1) {
                if (!isValidFP(sminor, "inverse flattening", false)) {
                    f1.minor.focus();
                    return false;
                }
                flat[i]=sminor;
                sminor=getSemiMinor(smaj, flat[i]);
                if (flat[i]<2) {
                    alert("The inverse flattening must be 2 or greater");
                    f1.minor.focus();
                    return false;
                }
            } else {
                if (!isValidFP(sminor, "polar radius", false)) {
                    f1.minor.focus();
                    return false;
                }
                flat[i]=smaj/(smaj-sminor);
                if (sminor < smaj/2) {
                    alert("The polar radius must not be less than one half the equatorial radius");
                    f1.minor.focus();
                    return false;
                }
                if (sminor >= smaj) {
                    alert("for ellipsoid models the polar radius must be smaller than the equatorial radius");
                    f1.minor.focus();
                    return false;
                }
            }
            var c= smaj*PI/500;
            if (f1.distunits[0].checked) {
                c=c/1.609344;
            }
            var l=floor(log(c)/log(10)) - 1;
            mx=ceil(c/pow(10, l))*pow(10, l);
            if (!checkMinMax(dist, 0, mx, "distance", units)) {
                f1.dist.focus();
                return false;
            }
        }

        if (i != 0) {
            destEllipse(startlat, startlon, brg[j], dist, smaj, sminor)
        } else {
            destSphere(startlat, startlon, brg[j], dist/radiusEarth);
        }
        if  (isNaN(point.finalBrg) || isNaN(point.lat) || isNaN(point.lon)) {
            alert("The destination point could not be calculated");
            return false;
        }

        point.lat = padZeroRight(deg(point.lat));
        point.lon = padZeroRight(deg(normalizeLongitude(point.lon)));
        point.finalBrg = deg(normalizeBearing(point.finalBrg));
        point.backBrg=deg(normalizeBearing(point.backBrg));
        f1.destination.value = "Latitude: " + decimalToDMS(point.lat, 1) + "   "+point.lat + "\n";
        f1.destination.value += "Longitude: " + decimalToDMS(point.lon, 0) + "   " + point.lon + "\n";
        f1.destination.value += "Final bearing: " + decimalToDMS(point.finalBrg, 2) + "   " + padZeroRight(point.finalBrg) + "\n";
        f1.destination.value += "Back bearing: " + decimalToDMS(point.backBrg, 2) + "   " + padZeroRight(point.backBrg);

    }
}

function getSemiMinor(smajor, flat) {
    return smajor-(smajor/flat);
}

function latLonToDecimal(ll, mn, mx, f) {
    var fail;
    ll = trim(ll);
    var 	msg= "invalid";
    if (ll == "") {
        msg= "required";
        fail=true;
    }
    if (parseFloat(ll, 10) != ll) {
        var ch=ll.substring(ll.length-1);
        ch=ch.toLowerCase();
        if (f != "bearing") {
            if (!/^\d{1,3}(\W{1,2}\d{1,2}){0,2}\W{0,2}(e|n|s|w)$/i.test(ll) || f=="latitude" && ch!="n"&&ch!="s" || f=="longitude"&&ch!="e"&&ch!="w") {
                fail = true;
            }
        } else {
            if (!/^\d{1,3}(\W{1,2}\d{1,2}){0,2}\W{0,3}$/i.test(ll)) {
                fail = true;
            }
        }
        if (!fail) {
            var dms = ll.split(/\D/gi);
            ll = dms[0];
            for (i=1; i<dms.length; i++) {
                if (dms[i] > 59) {
                    fail = true;
                }
                ll=ll*1+dms[i]/Math.pow(60, i);
            }
            if (ch == "s" || ch == "w") {
                ll=-ll;
            }
        }
    }
    if (!fail) {
        if (ll<mn || ll>mx) {
            msg="invalid. It must be a number between " + mn + " and " + mx + ".";
            fail=true;
        }
    }
    if (fail) {
        alert ('The ' + f + ' is ' + msg);
        return -999;
    } else {
        return ll;
    }
}

function padWithZero(s) {
    if (s<10) {
        s= "0" + s;
    }
    return s;
}

function padZeroRight(s) {
    if (sigDigits>8) {
        sigDigits=8;
    } else if (sigDigits < 5) {
        sigDigits=5;
    }
    s="" + Math.round(s*Math.pow(10, sigDigits))/Math.pow(10, sigDigits);
    var i = s.indexOf('.');
    var d=(s.length-i-1);
    if (i == -1) {
        return (s + ".00");
    } else if (d == 1) {
        return (s + "0");
    } else {
        return s;
    }
}

function decimalToDMS(l, type) {
// type lat=0 lon=1 brg=2
    var dir1="";
    if (type==1) {
        if (l<0) {
            dir1= "S";
        } else {
            dir1 = "N";
        }
    } else if (type==0) {
        if (l<0) {
            dir1= "W";
        } else {
            dir1= "E";
        }
    }
    l=Math.abs(Math.round(l*3600)/3600);
    var deg1= Math.floor(l);
    var temp=(l-deg1)*60;
    var min1=padWithZero(Math.floor(temp));
    temp=(temp-min1);
    var sec1=padWithZero(Math.round(temp*60));
    if (sec1 == 60) {
        sec1 = 59;
    }
    return Math.abs(deg1) + '\u00B0' + min1 + '\u2032' + sec1 + '\u2033' + dir1;
}

function viewMap() {
    if (f1.destination.value != "") {
        var url = "viewmap.html?" + point.lat + "&" + point.lon + "&" + gStartlat + "&" + gStartlon;
        f1.target = "mapwin";
        mapwin=window.open(url,"mapwin","menubar=1,toolbar=1,directories=1,location=1,status=1,resizable=1,scrollbars=1");
    } else {
        alert ('You must calculate the destination point before viewing a map');
    }
}

function destEllipse(lat1, lon1, brg, s, a, b) {
    with (Math) {
        if (f1.distunits[1].checked) {
            s *= 1000;
        } else {
            s *= 1609.344;
        }
        var ind = max(f1.model.selectedIndex, 1);
        var cs1, ds1, ss1, cs1m;
        var f = 1/flat[ind];
        var sb=sin(brg);
        var cb=cos(brg);
        var tu1=(1-f)*tan(lat1);
        var cu1=1/sqrt((1+tu1*tu1));
        var su1=tu1*cu1;
        var s2=atan2(tu1, cb);
        var sa = cu1*sb;
        var csa=1-sa*sa;
        var us=csa*(a*a - b*b)/(b*b);
        var A=1+us/16384*(4096+us*(-768+us*(320-175*us)));
        var B = us/1024*(256+us*(-128+us*(74-47*us)));
        var s1=s/(b*A);
        var s1p = 2*PI;
        while (abs(s1-s1p) > 1e-12) {
            cs1m=cos(2*s2+s1);
            ss1=sin(s1);
            cs1=cos(s1);
            ds1=B*ss1*(cs1m+B/4*(cs1*(-1+2*cs1m*cs1m)- B/6*cs1m*(-3+4*ss1*ss1)*(-3+4*cs1m*cs1m)));
            s1p=s1;
            s1=s/(b*A)+ds1;
        }
        var t=su1*ss1-cu1*cs1*cb;
        var lat2=atan2(su1*cs1+cu1*ss1*cb, (1-f)*sqrt(sa*sa + t*t));
        var l2=atan2(ss1*sb, cu1*cs1-su1*ss1*cb);
        var c=f/16*csa*(4+f*(4-3*csa));
        var l=l2-(1-c)*f*sa* (s1+c*ss1*(cs1m+c*cs1*(-1+2*cs1m*cs1m)));
        var d=atan2(sa, -t);
        point.finalBrg=d+2*PI;
        point.backBrg=d+PI;
        point.lat = lat2;
        point.lon = lon1+l;
    }
}

function destSphere(lat1, lon1, brg, dist) {
    with (Math) {
        point.lat = asin(sin(lat1)*cos(dist) + cos(lat1)*sin(dist)*cos(brg));
        point.lon = lon1 + atan2(sin(brg)*sin(dist)*cos(lat1), cos(dist)-sin(lat1)*sin(point.lat));
        var dLon = lon1-point.lon;
        var y = sin(dLon) * cos(lat1);
        var x = cos(point.lat)*sin(lat1) - sin(point.lat)*cos(lat1)*cos(dLon);
        var d=atan2(y, x);
        point.finalBrg = d+PI;
        point.backBrg=d+2*PI;
    }
}


var recipient="C" + "o" + "n" + "t" + "a" + "c" + "t"
var eml = "mailto:" + "&#100;&#101;" + "a" + "&#110;&#64;&#103;&#101;&#111;&#109;&#105;&#100;&#112;&#111;&#105;&#110;&#116;&#46;&#99;&#111;&#109;";

function e() {
    document.write('<a href=' + eml + '>' + recipient + '</a>');
}

google.setOnLoadCallback
(initialize);
