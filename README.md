# Lunch Calculator PHP

## How It's Working

**Class Calculator**

Serves data, in which will be need in the class properties. 
We have 4 properties in class. In the 3 of them we keep our incoming data(as array of strings) in several structures before giving output.

**Proprieties**

- $bill: private array, where we keep incoming string as arrays of BillingItem Class:

		[0]=>{	  		object(BillItem) (3) {    			["price"]=>    				float		      	["paid_by"]=>    				string    			["attendees"]=> array{				[0] => 
					string			}
		}
		

- $byLines: private array, where we keep data from $bills @prop as each line of incoming array as one array with counted debt of that line like:

		array(count of incoming lines) {  			[first line]=>  				array(1) {    					[creditor]=>    						array(1) {      						[debtor]=>      							float(debt to creditor)				   			    }   		     				}
   		     				
- $linedByBills: private array, where we keep data from @prop $byLines for printing it in the methods printBill and printOptimizedBill. In this @param we keep data structured like:

		array(1) {  				[debtor]=>  					array(1) {    						[creditor]=>    							float(debt)  						    } 					}
					
- $calculate: method calculate must called just once. this @prop keeping (float) 1(calculate was called) or 0(calculate didn't 			 called) 

**Methods**

- __construct: constructor of class. Taking array of string and, and using 		 BillItem Class serving data in @param  $bills.- calculate:  private method. Main function for getting data from @prop  			$bills giving the output to methods printBill and 				printOptimizedBill. Useing @props $calculate, $bills, 			$byLines, Returning @props $linedByBills. Using helper 			methods calculateLines, createExcel, findCurrentDebt.- calculateLines:   private method. Getting from @prop $bills arrays one 				by one from loop of method calculate. Counting debt				(how much must pay person to creditor) by everybody of 			one line(day), then deleting the debd of creditor from 			common price(creditor name is mentioned twice or more: 			as creditor and as debtor(s)). If person(not creditor) 			is in the array twice(was invited somebody) then his 				debt = debt * 2. return the counted array of that line			(day). - createExcel: calling from method calculate. Getting @param (array) 			 $byLines, slicing from end of @param array by array and 			 comparing sliced with first one using method 					 compare_array_keys. If keys same, summing sliced array with 			 first, if not, then adding sliced array as first array to 			 local @var $end_result. Finish output is adding @var 				 $end_result to @prop $byLines.- compare_array_keys: used in loop. getting to array from method 					  createExcel and returning (bool) true, if will be 				  same keys in 2 array, and (bool) false, if there 				  wouldn't same key.- sum_array_values: used in loop. getting 2 arrays. checking if keys are 				same, then summing matched values. return one array 				summed from 2 ones.- findCurrentDebt:  calling from method calculate in the loop. taking last 			array from @prop $byLines and making its count smaller 			by finding repeating creditors as array key and summing 			its values(debtors, debt). using method sumDebt to make 			sum. At finish we get clear @prop $byLines with only 				one creditor with his debtors and them debt to 					creditor- sumDebt :   getting @params $mainCreditor, $increasedDebtors, $otherCreditors, 		subtract values(finding and decreasing debt of creditors) 			and giving them to method createAsNeed to make end array 			comparablele with the exist method printBill.- createAsNeed: getting @params from  and changing structure of @prop 			  linedByBills for using it in the exist method printBill.- printBill:  public method. Print the each array which get from method 			calculate as string.- printOptimizedBill: public method. Is using method checkSame. Print array, 				  which was optimised(counted and deleted all debts, 				  which may repeated like: debtor has debt to person1, 			  debtor has debt to creditor, creditor has debt to 				  person1) as string.- checkSame: getting @params $mainDebtor, $debtor, $mainCreditors, $creditors from method printOptimizedBill. If finding two arrays(lines with debtors, creditors and debt) which can be optimised, then adding them as first array of @param linedByBills.
