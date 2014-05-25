package pro.dite;

import com.sun.tools.javac.util.Pair;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.Serializable;
import java.util.*;

public class CacheEntry implements Serializable
{
    final HashSet<String> index = new HashSet<String>();

    final HashSet<String> removed = new HashSet<String>();
    final HashSet<String> added = new HashSet<String>();
    final HashSet<Pair<String, String>> renamed = new HashSet<Pair<String, String>>();

    String author;

    public CacheEntry(RevCommit commit)
    {
        this.author = commit.getAuthorIdent().getEmailAddress();
    }

    public ArrayList<String> getSortedRemovals()
    {
        ArrayList<String> sorted = new ArrayList<String>();
        sorted.addAll(this.removed);
        Collections.sort(sorted);
        return sorted;
    }

    public ArrayList<String> getSortedAdds()
    {
        ArrayList<String> sorted = new ArrayList<String>();
        sorted.addAll(this.added);
        Collections.sort(sorted);
        return sorted;
    }

    public ArrayList<Pair<String, String>> getSortedRenames()
    {
        ArrayList<Pair<String, String>> sorted = new ArrayList<Pair<String, String>>();
        sorted.addAll(this.renamed);
        Collections.sort(sorted, new PairComparator());
        return sorted;
    }

    private class PairComparator implements Comparator<Pair<String, String>>
    {
        @Override
        public int compare(Pair<String, String> o1, Pair<String, String> o2)
        {
            return o1.fst.compareTo(o2.fst);
        }
    }

    public void compile()
    {
        ArrayList<String> intersection = new ArrayList<String>();
        intersection.add("");
        for (String rem : removed)
        {
            if (added.contains(rem))
            {
                intersection.add(rem);
            }
        }
        for (String inter : intersection)
        {
            added.remove(inter);
            removed.remove(inter);
        }
    }
}
